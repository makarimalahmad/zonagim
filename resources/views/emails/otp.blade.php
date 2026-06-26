<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { margin: 0; padding: 0; background-color: #0f172a; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .container { width: 100%; max-width: 600px; margin: 0 auto; background-color: #1e293b; color: #ffffff; border-radius: 12px; overflow: hidden; margin-top: 40px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
        .header { background-color: #0f172a; padding: 30px; text-align: center; border-bottom: 2px solid #eab308; }
        .logo { font-size: 24px; font-weight: bold; color: #eab308; text-decoration: none; }
        .content { padding: 40px 30px; text-align: center; }
        .otp-box { background-color: #0f172a; padding: 20px; font-size: 32px; letter-spacing: 5px; font-weight: bold; color: #eab308; border-radius: 8px; margin: 30px 0; display: inline-block; border: 1px solid #334155; }
        .text-muted { color: #94a3b8; font-size: 14px; line-height: 1.6; }
        .footer { padding: 20px; text-align: center; color: #64748b; font-size: 12px; background-color: #0f172a; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="#" class="logo">LapakAkunID</a>
        </div>
        <div class="content">
            <h1 style="margin-top: 0; color: #ffffff;">Verifikasi Akun</h1>
            <p class="text-muted">Terima kasih telah mendaftar. Gunakan kode OTP di bawah ini untuk menyelesaikan proses pendaftaran Anda.</p>
            
            <div class="otp-box">
                {{ $otp }}
            </div>

            <p class="text-muted">Kode ini hanya berlaku selama <strong>10 menit</strong>.<br>Jangan berikan kode ini kepada siapa pun.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} LapakAkunID. All rights reserved.
        </div>
    </div>
</body>
</html>
