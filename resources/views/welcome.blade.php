<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <!-- Styles -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Figtree', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            width: 100%;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 60px 40px;
            text-align: center;
        }
        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: white;
            font-weight: bold;
        }
        h1 {
            font-size: 48px;
            color: #1a202c;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .subtitle {
            font-size: 20px;
            color: #718096;
            margin-bottom: 40px;
            line-height: 1.6;
        }
        .links {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 40px;
        }
        .link {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
            font-size: 16px;
        }
        .link:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        .link.secondary {
            background: #e2e8f0;
            color: #4a5568;
        }
        .link.secondary:hover {
            background: #cbd5e0;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .info {
            margin-top: 50px;
            padding-top: 40px;
            border-top: 1px solid #e2e8f0;
        }
        .info-item {
            display: inline-block;
            margin: 10px 20px;
            color: #718096;
            font-size: 14px;
        }
        .info-item strong {
            color: #4a5568;
            display: block;
            margin-bottom: 5px;
            font-size: 16px;
        }
        @media (max-width: 640px) {
            .container {
                padding: 40px 20px;
            }
            h1 {
                font-size: 36px;
            }
            .subtitle {
                font-size: 18px;
            }
            .links {
                flex-direction: column;
            }
            .link {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">L</div>
        <h1>Welcome to Laravel</h1>
        <p class="subtitle">
            The PHP Framework for Web Artisans
        </p>
        
        <div class="links">
            <a href="https://laravel.com/docs" class="link" target="_blank">Documentation</a>
            <a href="https://laracasts.com" class="link secondary" target="_blank">Laracasts</a>
            <a href="https://laravel-news.com" class="link secondary" target="_blank">News</a>
            <a href="https://github.com/laravel/laravel" class="link secondary" target="_blank">GitHub</a>
        </div>

        <div class="info">
            <div class="info-item">
                <strong>Laravel</strong>
                {{ app()->version() }}
            </div>
            <div class="info-item">
                <strong>PHP</strong>
                {{ PHP_VERSION }}
            </div>
        </div>
    </div>
</body>
</html>

