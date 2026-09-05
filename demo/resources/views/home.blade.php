<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>htmx 4 · Laravel showcase</title>
    <style>
        :root { color-scheme: light dark; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font: 16px/1.6 system-ui, -apple-system, "Segoe UI", sans-serif;
            background: #f6f8fb; color: #1b2434;
        }
        @media (prefers-color-scheme: dark) {
            body { background: #121826; color: #e8edf5; }
        }
        main { max-width: 42rem; margin: 0 auto; padding: 4rem 1.5rem; }
        .eyebrow {
            font-size: .75rem; letter-spacing: .12em; text-transform: uppercase;
            color: #5b6b82; margin: 0 0 .4rem;
        }
        h1 { margin: 0 0 .4rem; letter-spacing: -.01em; }
        .lede { color: #5b6b82; margin: 0 0 2rem; }
        .card {
            border: 1px solid #dde4ee; border-radius: 12px;
            background: #fff; padding: 1.2rem 1.4rem;
        }
        @media (prefers-color-scheme: dark) {
            .card { background: #1a2233; border-color: #2b3a52; }
        }
        .card h2 { margin: 0 0 .6rem; font-size: 1.05rem; }
        .out { min-height: 3.2em; }
        button {
            font: inherit; border: 0; border-radius: 8px; cursor: pointer;
            background: #2b5fce; color: #fff; padding: .5rem 1.1rem;
        }
        button:hover { background: #1e46a0; }
    </style>
    <x-htmx::scripts />
</head>
<body>
    <main>
        <p class="eyebrow">imsus/laravel-htmx &middot; htmx 4.0.0</p>
        <h1>Laravel + htmx 4</h1>
        <p class="lede">
            Demo app skeleton. Client assets are the package&rsquo;s vendored
            htmx 4 build; every feature showcase page will land on top of this.
        </p>

        <section class="card">
            <h2>Round trip</h2>
            <div id="hello-out" class="out">
                <p>Press the button — the server&rsquo;s fragment swaps in here.</p>
            </div>
            <button
                hx-post="/hello"
                hx-target="#hello-out"
                hx-swap="innerHTML"
                hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
            >
                Say hello
            </button>
        </section>
    </main>
</body>
</html>
