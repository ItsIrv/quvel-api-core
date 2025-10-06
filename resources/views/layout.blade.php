<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name'))</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
        }

        .container {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
            width: 90%;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: {{ config('quvel.frontend.theme.primary_color', '#3b82f6') }};
            margin-bottom: 1rem;
        }

        .btn {
            display: inline-block;
            background: {{ config('quvel.frontend.theme.primary_color', '#3b82f6') }};
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-secondary {
            background: #6b7280;
            margin-left: 0.5rem;
        }

        .countdown {
            font-size: 2rem;
            font-weight: bold;
            color: {{ config('quvel.frontend.theme.primary_color', '#3b82f6') }};
            margin: 1rem 0;
        }

        .message {
            margin-bottom: 1.5rem;
            line-height: 1.6;
            color: #6b7280;
        }

        @media (max-width: 480px) {
            .container {
                padding: 1.5rem;
            }

            .btn {
                display: block;
                margin: 0.5rem 0;
            }

            .btn-secondary {
                margin-left: 0;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="container">
        <div class="logo">
            {{ config('app.name', 'App') }}
        </div>

        @yield('content')
    </div>

    @stack('scripts')
</body>
</html>