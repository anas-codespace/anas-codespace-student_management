<?php
require_once 'functions.php';
require_staff();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Generate Attendance QR</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
          rel="stylesheet">
    <link href="assets/css/styles.css" rel="stylesheet">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>
<body class="bg-light">
<div class="container py-4">
    <a href="staff-dashboard.php" class="btn btn-link">&larr; Back</a>

    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h4>Generate Attendance QR</h4>
                    <p class="text-muted">QR code is valid for exactly 5 minutes from server creation time.</p>

                    <div id="message" class="alert alert-info">
                        Enter class details to generate a QR code.
                    </div>

                    <form id="qrForm">
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" id="subject" class="form-control"
                                   maxlength="150" required>
                        </div>

                        <div class="mb-3">
                            <label for="period" class="form-label">Period</label>
                            <input type="text" id="period" class="form-control"
                                   maxlength="50" placeholder="Period 1" required>
                        </div>

                        <div class="mb-3">
                            <label for="section" class="form-label">Section</label>
                            <input type="text" id="section" class="form-control"
                                   maxlength="50" placeholder="BCA-A" required>
                        </div>

                        <button id="generateBtn" class="btn btn-primary w-100">
                            Generate QR Code
                        </button>
                    </form>

                    <div id="qrPanel" class="text-center mt-4" hidden>
                        <h5 id="qrSubject"></h5>
                        <div id="qrcode" class="d-flex justify-content-center my-3"></div>

                        <p><strong>Period:</strong> <span id="qrPeriod"></span></p>
                        <p><strong>Section:</strong> <span id="qrSection"></span></p>
                        <p><strong>Expires at:</strong> <span id="expiry"></span></p>
                        <h5>Time remaining: <span id="countdown">05:00</span></h5>

                        <p class="text-muted">Students must scan this QR before it expires.</p>

                        <button type="button" id="newQrBtn" class="btn btn-outline-primary">
                            Generate New QR
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    const form = document.getElementById('qrForm');
    const message = document.getElementById('message');
    const generateBtn = document.getElementById('generateBtn');
    const qrPanel = document.getElementById('qrPanel');

    let countdownTimer = null;
    let expiresAtMs = 0;
    let requestInProgress = false;

    function showMessage(text, type = 'info') {
        message.className = 'alert alert-' + type;
        message.textContent = text;
    }

    function expireQr() {
        clearInterval(countdownTimer);
        countdownTimer = null;

        document.getElementById('qrcode').replaceChildren();
        document.getElementById('countdown').textContent = '00:00';
        qrPanel.hidden = true;
        form.hidden = false;

        showMessage(
            'This QR code has expired. Generate a new QR code to continue.',
            'warning'
        );
    }

    function updateCountdown() {
        // Use the expiry timestamp returned by the server.
        const remainingMs = expiresAtMs - Date.now();

        if (remainingMs <= 0) {
            expireQr();
            return;
        }

        const totalSeconds = Math.ceil(remainingMs / 1000);
        const minutes = Math.floor(totalSeconds / 60);
        const seconds = totalSeconds % 60;

        document.getElementById('countdown').textContent =
            String(minutes).padStart(2, '0') + ':' +
            String(seconds).padStart(2, '0');
    }

    form.addEventListener('submit', async event => {
        event.preventDefault();

        if (requestInProgress) return;

        if (typeof QRCode === 'undefined') {
            showMessage('QR library failed to load. Check your internet connection.', 'danger');
            return;
        }

        requestInProgress = true;
        generateBtn.disabled = true;
        showMessage('Generating QR code...', 'info');

        try {
            const response = await fetch('create_qr_session.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    subject: document.getElementById('subject').value.trim(),
                    period: document.getElementById('period').value.trim(),
                    section: document.getElementById('section').value.trim()
                })
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.error || 'Could not generate QR code.');
            }

            /*
             * Backend must return expires_at as an ISO 8601 timestamp
             * containing a timezone, e.g. 2026-10-25T16:45:00Z.
             * Do not parse an ambiguous local date string here.
             */
            const parsedExpiry = Date.parse(data.expires_at);

            if (!Number.isFinite(parsedExpiry) || !data.token) {
                throw new Error(
                    'Server returned an invalid expiry time or QR token. Check create_qr_session.php.'
                );
            }

            expiresAtMs = parsedExpiry;

            if (expiresAtMs <= Date.now()) {
                throw new Error('The QR session is already expired. Generate a new one.');
            }

            document.getElementById('qrcode').replaceChildren();

            new QRCode(document.getElementById('qrcode'), {
                text: data.token,
                width: 240,
                height: 240,
                correctLevel: QRCode.CorrectLevel.M
            });

            document.getElementById('qrSubject').textContent = data.subject;
            document.getElementById('qrPeriod').textContent = data.period;
            document.getElementById('qrSection').textContent = data.section;
            document.getElementById('expiry').textContent =
                new Date(expiresAtMs).toLocaleString('en-IN', {
                    timeZone: 'Asia/Kolkata',
                    dateStyle: 'medium',
                    timeStyle: 'medium'
                });

            form.hidden = true;
            qrPanel.hidden = false;
            showMessage('QR code generated successfully.', 'success');

            clearInterval(countdownTimer);
            updateCountdown();
            countdownTimer = setInterval(updateCountdown, 250);

        } catch (error) {
            console.error('QR generation error:', error);
            showMessage(error.message || 'Unable to generate QR code.', 'danger');
        } finally {
            requestInProgress = false;
            generateBtn.disabled = false;
        }
    });

    document.getElementById('newQrBtn').addEventListener('click', () => {
        clearInterval(countdownTimer);
        countdownTimer = null;

        document.getElementById('qrcode').replaceChildren();
        qrPanel.hidden = true;
        form.hidden = false;
        showMessage('Enter class details to generate another QR.', 'info');
    });

    window.addEventListener('pagehide', () => {
        clearInterval(countdownTimer);
    });
})();
</script>
</body>
</html>
