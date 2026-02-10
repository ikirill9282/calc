<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сброс пароля</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .button {
            display: inline-block;
            background-color: #f44336;
            color: white !important;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin: 0 auto;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #777;
            text-align: center;
        }

        p {
          text-align: center;
        }

        div {
          text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Сброс пароля</h1>
        </div>
        <p>Здравствуйте, {{ $user->name }}!</p>
        <p>Вы получили это письмо, потому что запросили сброс пароля для своей учетной записи.</p>
        <p>Чтобы сбросить пароль, нажмите на кнопку ниже:</p>
        <a href="{{ $user->makeResetUrl() ?? '#' }}" class="button">Сбросить пароль</a>
        <p>Если вы не запрашивали сброс пароля, можете проигнорировать это письмо.</p>
        <div class="footer">
            <p>С уважением,<br> команда {{ env('APP_NAME') }}</p>
        </div>
    </div>
</body>
</html>