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
        .btn { display: inline-block; padding: 14px 30px; background-color: #eab308; color: #000000; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 20px 0; font-size: 16px; transition: background-color 0.3s; }
        .btn:hover { background-color: #ca8a04; }
        .text-muted { color: #94a3b8; font-size: 14px; line-height: 1.6; }
        .footer { padding: 20px; text-align: center; color: #64748b; font-size: 12px; background-color: #0f172a; }
        .link-text { color: #eab308; word-break: break-all; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="{{ config('app.url') }}" class="logo">{{ config('app.name') }}</a>
        </div>
        <div class="content">
            <h1 style="margin-top: 0; color: #ffffff;">Reset Password</h1>
            <p class="text-muted">Kami menerima permintaan untuk mereset password akun Anda.<br>Klik tombol di bawah ini untuk melanjutkan.</p>
            
            <a href="{{ $url }}" class="btn">Reset Password</a>

            <p class="text-muted">Link ini akan kadaluarsa dalam 60 menit.<br>Jika Anda tidak meminta reset password, abaikan email ini.</p>
            
            <hr style="border: 0; border-top: 1px solid #334155; margin: 30px 0;">
            
            <p class="text-muted" style="font-size: 12px;">
                Jika tombol di atas tidak berfungsi, salin dan tempel link berikut ke browser Anda:<br>
                <a href="{{ $url }}" class="link-text">{{ $url }}</a>
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
