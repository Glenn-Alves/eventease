<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Something went wrong | EventEase</title>
    <link rel="icon" href="/favicon.ico">
    <style>
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
               font-family: system-ui, sans-serif; background: #f3f4f6; color: #111827; }
        main { max-width: 32rem; margin: 1rem; padding: 2.5rem 2rem; background: #fff; border-radius: 12px;
               box-shadow: 0 1px 3px rgba(0, 0, 0, .12); text-align: center; }
        .code { font: 700 4.5rem/1 Georgia, serif; margin: 0; }
        h1 { font-size: 1.5rem; margin: .5rem 0; }
        p { color: #374151; line-height: 1.6; }
        a { display: inline-block; margin: .25rem; padding: .75rem 1.25rem; min-height: 44px; box-sizing: border-box;
            border-radius: 8px; border: 1px solid #0f172a; color: #0f172a; text-decoration: none; font-weight: 600; }
        a.primary { background: #0f172a; color: #fff; }
        a:focus-visible { outline: 3px solid #4f46e5; outline-offset: 2px; }
    </style>
</head>
<body>
    <main>
        <p class="code" aria-hidden="true">500</p>
        <h1>Something went wrong</h1>
        <p>This is a problem on our side, not yours. Please try again in a moment. If it keeps happening, tell the admin what you were doing.</p>
        <a class="primary" href="{{ url('/') }}">Go to the home page</a>
    </main>
</body>
</html>