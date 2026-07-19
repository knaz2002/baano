<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подтверждение email</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .logo {
            max-width: 150px;
            height: auto;
        }
        .content {
            padding: 40px 30px;
            color: #1D1B20;
        }
        .title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #1D1B20;
        }
        .text {
            font-size: 16px;
            line-height: 1.6;
            color: #49454F;
            margin-bottom: 30px;
        }
        .button {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #F08080 0%, #9B7FCF 100%);
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            margin: 20px 0;
            transition: transform 0.2s;
        }
        .button:hover {
            transform: translateY(-2px);
        }
        .footer {
            background-color: #F8F7FA;
            padding: 30px;
            text-align: center;
            color: #6750A4;
            font-size: 14px;
        }
        .help-text {
            font-size: 14px;
            color: #6B7280;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #E7E0EC;
        }
        .url-text {
            word-break: break-all;
            color: #6750A4;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Шапка с логотипом -->
        <div class="header">
            <img src="{{ asset('images/logo.png') }}" alt="Baano" class="logo">
        </div>

        <!-- Основной контент -->
        <div class="content">
            <div class="title">Здравствуйте!</div>
            
            <div class="text">
                Спасибо за регистрацию на <strong>Baano</strong>! Пожалуйста, подтвердите свой адрес электронной почты, нажав на кнопку ниже.
            </div>

            <div style="text-align: center;">
                <a href="{{ $url }}" class="button">Подтвердить email</a>
            </div>

            <div class="text">
                Если вы не создавали аккаунт, никаких дополнительных действий не требуется.
            </div>

            <div class="help-text">
                Если у вас возникли проблемы с нажатием кнопки "Подтвердить email", скопируйте и вставьте эту ссылку в браузер:
                <br><br>
                <span class="url-text">{{ $url }}</span>
            </div>
        </div>

        <!-- Подвал -->
        <div class="footer">
            © {{ date('Y') }} Baano. Все права защищены.
        </div>
    </div>
</body>
</html>
