<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>403 - Forbidden</title>
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

        .illustration {
            width: 160px;
            height: 160px;
            background: #fff3cd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
        }

        .illustration svg {
            width: 80px;
            height: 80px;
            fill: #e67700;
        }

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
                <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
            </svg>
        </div>

        {{-- Error code --}}
        <div class="error-code">403</div>
        <div class="error-title">Access Forbidden</div>
        <p class="error-message">
            You don't have permission to access this page.
            Please contact your administrator if you believe this is a mistake.
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
