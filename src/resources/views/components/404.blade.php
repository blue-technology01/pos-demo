<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Page Not Found</title>
    <link rel="stylesheet" href="{{ asset('assets/css/errors/error.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
</head>
<body>
    <div class="error-wrap">
        <div class="error-code blue">404</div>
        <div class="error-divider"></div>
        <h1 class="error-title">Page not found</h1>
        <p class="error-desc">
            The page you're looking for doesn't exist or has been moved.
        </p>
        <a href="{{ url('/') }}" class="btn-back">
            <i class="ti ti-arrow-left"></i> Back to home
        </a>
    </div>
</body>
</html>
