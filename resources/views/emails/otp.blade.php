<!DOCTYPE html>
<html lang="id" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>Kode Verifikasi LapakAkunID</title>
</head>
<body style="margin:0; padding:0; width:100%; background-color:#f4f5f7; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%;">
    {{-- Preheader: teks ringkas yang muncul di preview inbox (disembunyikan di body) --}}
    <div style="display:none; max-height:0; overflow:hidden; opacity:0; mso-hide:all;">
        Kode verifikasi kamu: {{ $otp }}. Berlaku 10 menit.
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f4f5f7;">
        <tr>
            <td align="center" style="padding:32px 16px;">

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:480px; background-color:#ffffff; border:1px solid #e5e7eb; border-radius:14px; overflow:hidden; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">

                    {{-- Brand --}}
                    <tr>
                        <td style="padding:30px 32px 0; text-align:center;">
                            <span style="font-size:20px; font-weight:700; letter-spacing:.2px; color:#0b1221;">Lapak<span style="color:#eab308;">AkunID</span></span>
                        </td>
                    </tr>

                    {{-- Judul + deskripsi --}}
                    <tr>
                        <td style="padding:22px 32px 4px; text-align:center;">
                            <h1 style="margin:0 0 8px; font-size:20px; line-height:1.3; color:#0b1221; font-weight:700;">Kode Verifikasi Akun</h1>
                            <p style="margin:0; font-size:14px; line-height:1.6; color:#64748b;">Masukkan kode di bawah ini untuk menyelesaikan pendaftaran akunmu di LapakAkunID.</p>
                        </td>
                    </tr>

                    {{-- Kode OTP --}}
                    <tr>
                        <td align="center" style="padding:24px 32px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="background-color:#f8fafc; border:1px solid #e5e7eb; border-radius:12px; padding:18px 30px;">
                                        <span style="font-size:34px; font-weight:700; letter-spacing:10px; color:#0b1221; font-family:'Courier New',Courier,monospace;">{{ $otp }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Catatan keamanan --}}
                    <tr>
                        <td style="padding:0 32px 4px; text-align:center;">
                            <p style="margin:0; font-size:13px; line-height:1.6; color:#64748b;">Kode berlaku selama <strong style="color:#0b1221;">10 menit</strong>. Demi keamanan, jangan bagikan kode ini kepada siapa pun.</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 32px 0;">
                            <hr style="border:none; border-top:1px solid #eef0f3; margin:0; height:1px; line-height:1px;">
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px 32px 28px; text-align:center;">
                            <p style="margin:0; font-size:12px; line-height:1.6; color:#94a3b8;">Kamu menerima email ini karena alamat ini dipakai untuk mendaftar di LapakAkunID. Jika ini bukan kamu, cukup abaikan email ini.</p>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="padding:18px 32px; background-color:#fafbfc; border-top:1px solid #eef0f3; text-align:center;">
                            <p style="margin:0; font-size:12px; color:#94a3b8;">&copy; {{ date('Y') }} LapakAkunID &middot; lapakgim.my.id</p>
                        </td>
                    </tr>

                </table>

                <p style="max-width:480px; margin:16px auto 0; font-size:11px; line-height:1.5; color:#b6bdc7; text-align:center; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">Email otomatis &mdash; mohon tidak membalas.</p>

            </td>
        </tr>
    </table>
</body>
</html>
