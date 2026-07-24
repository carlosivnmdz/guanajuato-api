<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify your account</title>
</head>

<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 0;">
        <tr>
            <td align="center">

                <table width="600" cellpadding="0" cellspacing="0"
                    style="background:#ffffff;border-radius:18px;overflow:hidden;box-shadow:0 10px 35px rgba(0,0,0,.08);">

                    <!-- Header -->
                    <tr>
                        <td align="center"
                            style="background:linear-gradient(135deg,#f97316,#ea580c);padding:35px;">

                            <h1 style="margin:0;color:#ffffff;font-size:34px;">
                                🛍 Guanajuato
                            </h1>

                            <p style="margin-top:10px;color:#FFE7D6;font-size:16px;">
                                Secure Verification
                            </p>

                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:45px;">

                            <h2 style="margin-top:0;color:#111827;">
                                Verify your email
                            </h2>

                            <p style="font-size:16px;color:#4b5563;line-height:28px;">
                                Welcome to <strong>Guanajuato</strong>.
                                To continue using the application, enter the following verification code.
                            </p>

                            <div
                                style="margin:35px 0;padding:30px;background:#fff7ed;border:2px dashed #f97316;border-radius:16px;text-align:center;">

                                <div style="font-size:14px;color:#6b7280;margin-bottom:10px;">
                                    Verification Code
                                </div>

                                <div
                                    style="font-size:46px;font-weight:bold;letter-spacing:14px;color:#ea580c;">
                                    {{ $code }}
                                </div>

                            </div>

                            <div
                                style="background:#f9fafb;border-left:5px solid #f97316;padding:18px;border-radius:10px;">

                                ⏳
                                <strong>This code expires in 5 minutes.</strong>

                            </div>

                            <p
                                style="margin-top:35px;font-size:15px;color:#6b7280;line-height:26px;">

                                Never share this code with anyone.
                                Guanajuato will never ask for your verification code by phone or email.

                            </p>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td
                            style="background:#111827;padding:25px;text-align:center;color:#9ca3af;font-size:13px;">

                            © {{ date('Y') }} Guanajuato

                            <br><br>

                            This is an automated email. Please do not reply.

                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>