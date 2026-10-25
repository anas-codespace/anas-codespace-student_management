<?php
require_once 'functions.php';
require_student();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student QR Attendance</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
          rel="stylesheet">
    <link href="assets/css/styles.css" rel="stylesheet">

    <script src="assets/js/html5-qrcode.min.js"></script>
</head>

<body class="bg-light">
<div class="container py-4">

    <a href="student-dashboard.php" class="btn btn-link">
        &larr; Back to Dashboard
    </a>

    <div class="card shadow-sm">
        <div class="card-body p-4">

            <h4>Scan Attendance QR</h4>
            <p class="text-muted">
                Allow camera and location permissions when requested.
            </p>

            <div id="status" class="alert alert-info" role="status">
                Initializing scanner...
            </div>

            <div id="reader" style="width:100%;max-width:450px;margin:auto"></div>

            <button type="button" id="switchCamera"
                    class="btn btn-outline-secondary mt-3" hidden>
                Switch Camera
            </button>

            <div id="after-scan" class="mt-4" hidden>

                <p><strong>QR Token:</strong>
                    <span id="token"></span>
                </p>

                <p><strong>GPS:</strong>
                    <span id="gps-status">Waiting for location...</span>
                </p>

                <video id="video" autoplay muted playsinline
                       style="display:block;width:100%;max-width:320px;border:1px solid #ccc">
                </video>

                <canvas id="canvas" width="640" height="480" hidden></canvas>

                <div class="mt-3 d-flex gap-2 flex-wrap">
                    <button type="button" id="captureBtn"
                            class="btn btn-secondary">
                        Capture Selfie
                    </button>

                    <button type="button" id="retakeBtn"
                            class="btn btn-outline-secondary" hidden>
                        Retake Selfie
                    </button>

                    <button type="button" id="submitBtn"
                            class="btn btn-primary" disabled>
                        Submit Attendance
                    </button>
                </div>

                <img id="preview" alt="Captured selfie"
                     style="display:none;width:160px;margin-top:12px;border-radius:8px">
            </div>

        </div>
    </div>
</div>

<script>
(() => {
    const statusBox = document.getElementById('status');
    const readerElement = document.getElementById('reader');
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');

    const captureBtn = document.getElementById('captureBtn');
    const retakeBtn = document.getElementById('retakeBtn');
    const submitBtn = document.getElementById('submitBtn');
    const switchBtn = document.getElementById('switchCamera');

    let scanner = null;
    let cameras = [];
    let cameraIndex = 0;
    let scannerRunning = false;
    let startingScanner = false;
    let token = '';
    let latitude = null;
    let longitude = null;
    let selfieCaptured = false;
    let selfieStream = null;
    let submitting = false;

    function setStatus(message, type = 'info') {
        statusBox.className = 'alert alert-' + type;
        statusBox.textContent = message;
    }

    function updateSubmitButton() {
        submitBtn.disabled = !(
            token &&
            latitude !== null &&
            longitude !== null &&
            selfieCaptured &&
            !submitting
        );
    }

    function stopSelfieCamera() {
        if (selfieStream) {
            selfieStream.getTracks().forEach(track => track.stop());
            selfieStream = null;
        }

        video.srcObject = null;
    }

    async function startScanner() {
        if (startingScanner || scannerRunning || token) return;

        startingScanner = true;

        try {
            if (!window.isSecureContext) {
                throw new Error(
                    'Camera access requires HTTPS or localhost.'
                );
            }

            if (typeof Html5Qrcode === 'undefined') {
                throw new Error(
                    'QR scanner library did not load from assets/js/html5-qrcode.min.js. Check the file path and server response.'
                );
            }

            setStatus('Requesting camera permission...', 'info');

            scanner = scanner || new Html5Qrcode('reader');

            cameras = await Promise.race([
                Html5Qrcode.getCameras(),
                new Promise((_, reject) =>
                    setTimeout(
                        () => reject(new Error(
                            'Camera detection timed out. Check camera permission.'
                        )),
                        12000
                    )
                )
            ]);

            if (!cameras || cameras.length === 0) {
                throw new Error(
                    'No camera detected. Check your camera connection and browser permission.'
                );
            }

            // Prefer a back/environment camera when available.
            const backIndex = cameras.findIndex(camera =>
                /back|rear|environment|world/i.test(camera.label || '')
            );

            cameraIndex = backIndex >= 0 ? backIndex : 0;

            setStatus('Starting QR camera...', 'info');

            await scanner.start(
                cameras[cameraIndex].id,
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1
                },
                onScanSuccess,
                () => {}
            );

            scannerRunning = true;
            switchBtn.hidden = cameras.length < 2;

            setStatus(
                'Camera ready. Point it at the staff QR code.',
                'success'
            );

        } catch (error) {
            console.error('QR scanner error:', error);

            setStatus(
                'Camera failed: ' +
                (error.message || 'Unknown camera error') +
                ' Check browser permissions and close other camera apps.',
                'danger'
            );

        } finally {
            startingScanner = false;
        }
    }

    async function onScanSuccess(decodedText) {
        if (token || !decodedText.trim()) return;

        token = decodedText.trim();

        document.getElementById('token').textContent = token;
        document.getElementById('after-scan').hidden = false;

        setStatus(
            'QR scanned. Starting GPS and selfie camera...',
            'info'
        );

        try {
            if (scannerRunning) {
                await scanner.stop();
                scannerRunning = false;
            }

            scanner.clear();
            readerElement.hidden = true;
            switchBtn.hidden = true;

        } catch (error) {
            console.warn('Could not stop scanner:', error);
        }

        getLocation();
        startSelfieCamera();
    }

    switchBtn.addEventListener('click', async () => {
        if (cameras.length < 2 || scannerRunning || startingScanner || token) {
            return;
        }

        cameraIndex = (cameraIndex + 1) % cameras.length;

        try {
            startingScanner = true;
            setStatus('Switching camera...', 'info');

            await scanner.start(
                cameras[cameraIndex].id,
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1
                },
                onScanSuccess,
                () => {}
            );

            scannerRunning = true;
            setStatus('Camera ready. Scan the staff QR code.', 'success');

        } catch (error) {
            setStatus('Unable to switch camera: ' + error.message, 'danger');
        } finally {
            startingScanner = false;
        }
    });

    function getLocation() {
        const gpsStatus = document.getElementById('gps-status');

        if (!navigator.geolocation) {
            gpsStatus.textContent = 'Geolocation is not supported.';
            return;
        }

        gpsStatus.textContent = 'Requesting location permission...';

        navigator.geolocation.getCurrentPosition(
            position => {
                latitude = position.coords.latitude;
                longitude = position.coords.longitude;

                gpsStatus.textContent =
                    latitude.toFixed(6) + ', ' +
                    longitude.toFixed(6) +
                    ' (accuracy: ' +
                    Math.round(position.coords.accuracy) + ' m)';

                updateSubmitButton();
            },
            error => {
                latitude = null;
                longitude = null;

                gpsStatus.textContent =
                    'Location error: ' + error.message;

                setStatus(
                    'Location is required. Enable browser location permission and try again.',
                    'warning'
                );

                updateSubmitButton();
            },
            {
                enableHighAccuracy: true,
                timeout: 20000,
                maximumAge: 0
            }
        );
    }

    async function startSelfieCamera() {
        stopSelfieCamera();

        try {
            if (!navigator.mediaDevices?.getUserMedia) {
                throw new Error(
                    'Selfie camera requires HTTPS or localhost.'
                );
            }

            setStatus('Opening selfie camera...', 'info');

            selfieStream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'user',
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                },
                audio: false
            });

            video.srcObject = selfieStream;
            await video.play();

            setStatus(
                'Selfie camera ready. Capture your selfie.',
                'success'
            );

        } catch (error) {
            console.error('Selfie camera error:', error);

            setStatus(
                'Selfie camera failed: ' + error.message,
                'danger'
            );
        }
    }

    captureBtn.addEventListener('click', () => {
        if (!video.videoWidth || !video.videoHeight) {
            setStatus(
                'Camera is not ready. Allow access and wait for the video.',
                'warning'
            );
            return;
        }

        const context = canvas.getContext('2d');

        context.drawImage(
            video,
            0,
            0,
            canvas.width,
            canvas.height
        );

        selfieCaptured = true;

        const preview = document.getElementById('preview');
        preview.src = canvas.toDataURL('image/jpeg', 0.85);
        preview.style.display = 'block';

        captureBtn.hidden = true;
        retakeBtn.hidden = false;

        stopSelfieCamera();
        updateSubmitButton();

        setStatus(
            'Selfie captured. Check GPS and submit attendance.',
            'success'
        );
    });

    retakeBtn.addEventListener('click', async () => {
        selfieCaptured = false;

        document.getElementById('preview').style.display = 'none';
        captureBtn.hidden = false;
        retakeBtn.hidden = true;

        updateSubmitButton();
        await startSelfieCamera();
    });

    submitBtn.addEventListener('click', async () => {
        if (
            !token ||
            latitude === null ||
            longitude === null ||
            !selfieCaptured ||
            submitting
        ) {
            return;
        }

        submitting = true;
        updateSubmitButton();

        setStatus('Submitting attendance...', 'info');

        try {
            const response = await fetch('mark_attendance.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    token: token,
                    latitude: latitude,
                    longitude: longitude,
                    selfie: canvas.toDataURL('image/jpeg', 0.85)
                })
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(
                    result.error || 'Attendance submission failed.'
                );
            }

            setStatus('Attendance recorded successfully.', 'success');
            stopSelfieCamera();

            window.location.href = 'student-dashboard.php';

        } catch (error) {
            console.error('Attendance submission error:', error);

            setStatus(
                error.message || 'Network error while submitting attendance.',
                'danger'
            );

        } finally {
            submitting = false;
            updateSubmitButton();
        }
    });

    window.addEventListener('pagehide', () => {
        stopSelfieCamera();

        if (scannerRunning && scanner) {
            scanner.stop().catch(() => {});
        }
    });

    // The local library script must load before this code runs.
    startScanner();
})();
</script>

</body>
</html>