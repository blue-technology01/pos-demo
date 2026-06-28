<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>404 - Page Not Found</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            font-family: 'Segoe UI', sans-serif;
        }

        .error-page {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 16px;
            text-align: center;
            padding: 40px 20px;
        }

        /* ── Illustration ── */
        .illustration {
            width: 160px;
            height: 160px;
            background: #e9ecef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
        }

        .illustration svg {
            width: 80px;
            height: 80px;
            fill: #adb5bd;
        }

        /* ── Error text ── */
        .error-code {
            font-size: 72px;
            font-weight: 800;
            color: #212529;
            line-height: 1;
            letter-spacing: -2px;
        }

        .error-title {
            font-size: 22px;
            font-weight: 600;
            color: #343a40;
        }

        .error-message {
            font-size: 14px;
            color: #868e96;
            max-width: 360px;
            line-height: 1.6;
        }

        /* ── Buttons ── */
        .btn-row {
            display: flex;
            gap: 12px;
            margin-top: 8px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.18s;
            border: none;
        }

        .btn svg {
            width: 18px;
            height: 18px;
            fill: currentColor;
            flex-shrink: 0;
        }

        .btn-ghost {
            background: #f1f3f5;
            color: #495057;
            border: 1px solid #dee2e6;
        }

        .btn-ghost:hover {
            background: #e9ecef;
            color: #2563a8;
            border-color: #2563a8;
        }

        .btn-primary {
            background: #2563a8;
            color: #ffffff;
        }

        .btn-primary:hover {
            background: #1d4e8f;
        }

        .btn-primary svg {
            fill: #ffffff;
        }
    </style>
</head>
<body>
    <div class="error-page">

        {{-- Illustration --}}
        <div class="illustration">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
            </svg>
        </div>

        {{-- Error code --}}
        <div class="error-code">404</div>
        <div class="error-title">Page Not Found</div>
        <p class="error-message">
            Oops! The page you're looking for doesn't exist or has been moved.
            Please check the URL or go back to the homepage.
        </p>

        {{-- Buttons --}}
        <div class="btn-row">
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('login') }}" class="btn btn-ghost">
                <svg viewBox="0 0 24 24">
                    <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
                </svg>
                Go Back
            </a>
            <a href="{{ route('login') }}" class="btn btn-primary">
                <svg viewBox="0 0 24 24">
                    <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                </svg>
                Go Home
            </a>
        </div>

    </div>
</body>
</html>
