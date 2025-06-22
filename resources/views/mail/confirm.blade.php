<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Подтверждение почты</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

<style>
  * {
    font-family: inherit;
  }

  body {
    font-family: "Roboto", sans-serif;
  }
</style>
</head>
<body style="background-color:#f3f4f6; margin:0; padding:0;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6; min-height:100vh; padding: 40px 0;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 420px; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); padding: 32px;">
                    <tr>
                        <td align="center" style="padding-bottom: 24px;">
                            <div style="font-size: 32px; font-weight: 700; color: #009966; margin-bottom: 8px;">{{ config('app.name') }}</div>
                            <div style="font-size: 18px; color: #374151;">Подтверждение почты</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 16px; color: #374151; padding-bottom: 24px;">
                            Здравствуйте, <span style="font-weight:600;">{{ $user->name }}</span>!<br><br>
                            Пожалуйста, подтвердите ваш адрес электронной почты, нажав на кнопку ниже:
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding-bottom: 32px;">
                            <a href="{{ $user->getVerificationUrl() }}" style="display:inline-block; background-color:#009966; color:#fff; font-weight:600; padding:14px 32px; border-radius:8px; text-decoration:none; font-size:16px; box-shadow:0 1px 3px rgba(37,99,235,0.15);">
                                Подтвердить Email
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px; color: #6b7280; padding-bottom: 16px;">
                            Если вы не регистрировались на нашем сайте, просто проигнорируйте это письмо.
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px; color: #6b7280;">
                            Спасибо,<br>
                            <span style="color:#009966;">{{ config('app.name') }}</span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>