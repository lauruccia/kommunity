<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · Kommunity</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:300,400,500,600,700&display=swap" rel="stylesheet"/>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(circle at top left, rgba(70,93,112,0.14), transparent 34%),
                radial-gradient(circle at top right, rgba(85,121,79,0.16), transparent 30%),
                linear-gradient(180deg, #f5f8f8 0%, #edf3f3 42%, #e6edee 100%);
            color: #18222b;
            -webkit-font-smoothing: antialiased;
            padding: 1.5rem;
        }
        .card {
            background: #f8fbfb;
            border: 1px solid rgba(68,86,98,0.14);
            border-radius: 28px;
            box-shadow: 0 24px 60px rgba(28,39,51,0.08);
            padding: 3rem 2.5rem;
            max-width: 480px;
            width: 100%;
            text-align: center;
        }
        .code {
            font-size: 5rem;
            font-weight: 800;
            letter-spacing: -0.04em;
            line-height: 1;
            background: linear-gradient(135deg, #465d70 0%, #55794f 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }
        h1 {
            font-size: 1.35rem;
            font-weight: 600;
            color: #18222b;
            margin-bottom: 0.75rem;
            letter-spacing: -0.025em;
        }
        p {
            font-size: 0.9rem;
            color: #60717c;
            line-height: 1.7;
            margin-bottom: 2rem;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border-radius: 999px;
            padding: 0.7rem 1.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            background: linear-gradient(135deg, #55794f 0%, #426240 100%);
            color: #fff;
            box-shadow: 0 12px 28px rgba(66,98,64,0.22);
        }
        .btn:hover { background: linear-gradient(135deg, #4f704b 0%, #39563a 100%); }
        .brand {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 2rem;
            text-decoration: none;
        }
        .brand-mark {
            width: 2.75rem; height: 2.75rem;
            border-radius: 1.15rem;
            border: 1px solid rgba(70,93,112,0.16);
            background: #fff;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 8px 20px rgba(28,39,51,0.08);
        }
        .brand-mark img {
            width: 1.55rem;
            height: 2.35rem;
            object-fit: contain;
            display: block;
        }
        .brand-name { font-size: 1rem; font-weight: 600; color: #18222b; }
    </style>
</head>
<body>
    <div class="card">
        <a href="{{ url('/') }}" class="brand">
            <div class="brand-mark">
                <img src="{{ asset('brand/kommunity-logo.png') }}" alt="Kommunity">
            </div>
            <span class="brand-name">Kommunity</span>
        </a>
        <div class="code">@yield('code')</div>
        <h1>@yield('title')</h1>
        <p>@yield('message')</p>
        <a href="{{ url('/') }}" class="btn">
            ← Torna alla home
        </a>
    </div>
</body>
</html>
