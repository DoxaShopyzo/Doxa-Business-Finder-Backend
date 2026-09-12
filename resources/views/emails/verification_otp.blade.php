<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Verify your email address</title>
    <!--[if mso]>
    <style type="text/css">
        body, table, td, a { font-family: Arial, Helvetica, sans-serif !important; }
    </style>
    <![endif]-->
</head>
<body style="margin: 0; padding: 0; background-color: #f4f6f8; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <!-- Preview Text (Hidden in email body) -->
    <div style="display: none; font-size: 1px; color: #fefefe; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden;">
        Your Doxa Business Finder verification code is {{ $otp }}. Valid for 10 minutes.
    </div>

    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="table-layout: fixed; background-color: #f4f6f8; padding: 30px 0;">
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 540px; background-color: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);">
                    
                    <!-- Header -->
                    <tr>
                        <td align="center" style="background-color: #1E3A5F; padding: 28px 20px;">
                            <table border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <div style="font-size: 22px; font-weight: bold; color: #ffffff; letter-spacing: 0.5px;">
                                            Doxa Business Finder
                                        </div>
                                        <div style="font-size: 12px; color: #93c5fd; margin-top: 4px; text-transform: uppercase; letter-spacing: 1px;">
                                            Account Security
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 32px 32px 24px 32px; color: #334155;">
                            <h1 style="font-size: 20px; font-weight: bold; color: #0f172a; margin: 0 0 16px 0;">
                                Verify your email address
                            </h1>
                            <p style="font-size: 15px; line-height: 24px; color: #475569; margin: 0 0 16px 0;">
                                Hello <strong>{{ $userName }}</strong>,
                            </p>
                            <p style="font-size: 15px; line-height: 24px; color: #475569; margin: 0 0 24px 0;">
                                To complete your registration and secure your Doxa Business Finder account, please enter the following 6-digit one-time code:
                            </p>

                            <!-- OTP Box -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 24px;">
                                <tr>
                                    <td align="center" style="background-color: #f8fafc; border: 2px dashed #0EA5E9; border-radius: 10px; padding: 18px 24px;">
                                        <div style="font-family: 'Courier New', Courier, monospace; font-size: 34px; font-weight: bold; letter-spacing: 8px; color: #1E3A5F;">
                                            {{ $otp }}
                                        </div>
                                        <div style="font-size: 12px; color: #64748b; margin-top: 6px; font-weight: 500;">
                                            Expires in 10 minutes
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size: 14px; line-height: 22px; color: #64748b; margin: 0 0 12px 0;">
                                <strong>Important:</strong> Never share this code with anyone. Doxa support will never ask for your verification code.
                            </p>
                            <p style="font-size: 13px; line-height: 20px; color: #94a3b8; margin: 0;">
                                If you did not request this code, you can safely ignore this email or contact our support team.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; border-top: 1px solid #f1f5f9; padding: 20px 32px; text-align: center;">
                            <p style="font-size: 12px; line-height: 18px; color: #94a3b8; margin: 0 0 6px 0;">
                                Sent by <strong>Doxa Infotech</strong> • Business Lead Generation & CRM
                            </p>
                            <p style="font-size: 11px; line-height: 16px; color: #cbd5e1; margin: 0;">
                                © {{ date('Y') }} Doxa Infotech Pvt. Ltd. All rights reserved.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
