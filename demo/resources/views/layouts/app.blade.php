<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'htmx 4 patterns · Laravel showcase')</title>
    <script>
        // Apply the saved theme before first paint so there is no flash of
        // the wrong scheme. Defaults to the OS preference on first visit.
        (() => {
            const theme = localStorage.getItem('theme')
                ?? (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.dataset.theme = theme;
        })();
    </script>
    <style>
        :root {
            color-scheme: light;
            --bg: #f6f8fb; --fg: #1b2434; --muted: #5b6b82;
            --card: #ffffff; --border: #dde4ee; --border-soft: #e3e9f2;
            --row: #fbfcfe;
            --accent: #2b5fce; --accent-hover: #1e46a0; --link: #2b5fce;
            --chip: rgba(91, 107, 130, .14);
            --sk-shine: rgba(255, 255, 255, .8);
        }
        html[data-theme='dark'] {
            color-scheme: dark;
            --bg: #121826; --fg: #e8edf5; --muted: #9aa8bd;
            --card: #1a2233; --border: #2b3a52; --border-soft: #26344b;
            --row: #151d2e;
            --accent: #5b8def; --accent-hover: #3d72e0; --link: #8ab0ff;
            --chip: rgba(148, 163, 184, .18);
            --sk-shine: rgba(255, 255, 255, .14);
        }
        /* No-JS fallback: follow the OS when no explicit theme was stored. */
        @media (prefers-color-scheme: dark) {
            :root:not([data-theme='light']) {
                color-scheme: dark;
                --bg: #121826; --fg: #e8edf5; --muted: #9aa8bd;
                --card: #1a2233; --border: #2b3a52; --border-soft: #26344b;
                --row: #151d2e;
                --accent: #5b8def; --accent-hover: #3d72e0; --link: #8ab0ff;
                --chip: rgba(148, 163, 184, .18);
                --sk-shine: rgba(255, 255, 255, .14);
            }
        }
        html[data-theme='light'] {
            color-scheme: light;
            --bg: #f6f8fb; --fg: #1b2434; --muted: #5b6b82;
            --card: #ffffff; --border: #dde4ee; --border-soft: #e3e9f2;
            --row: #fbfcfe;
            --accent: #2b5fce; --accent-hover: #1e46a0; --link: #2b5fce;
            --chip: rgba(91, 107, 130, .14);
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font: 16px/1.6 system-ui, -apple-system, "Segoe UI", sans-serif;
            background: var(--bg); color: var(--fg);
        }
        a { color: var(--link); }

        .site {
            max-width: 46rem; margin: 0 auto; padding: 2.2rem 1.5rem 0;
            display: flex; justify-content: space-between; align-items: center; gap: 1rem;
        }
        .site-right { display: flex; align-items: center; gap: .9rem; }
        .brand { font-weight: 650; letter-spacing: -.01em; color: inherit; text-decoration: none; }
        .brand span { color: var(--muted); margin: 0 .15em; }
        .brand-note { font-size: .75rem; color: var(--muted); letter-spacing: .06em; text-transform: uppercase; }

        .theme-toggle {
            display: inline-flex; align-items: center; gap: .35rem;
            font-size: .82rem; color: var(--fg);
            background: transparent; border: 1px solid var(--border);
            padding: .28rem .7rem;
        }
        .theme-toggle:hover { border-color: var(--accent); background: var(--chip); }
        .theme-toggle .theme-dark { display: none; }
        html[data-theme='dark'] .theme-toggle .theme-light { display: none; }
        html[data-theme='dark'] .theme-toggle .theme-dark { display: inline; }
        .theme-toggle svg { width: 14px; height: 14px; flex: none; }

        main { max-width: 46rem; margin: 0 auto; padding: 2.6rem 1.5rem 5rem; }

        .crumbs { font-size: .9rem; color: var(--muted); margin: 0 0 1.6rem; }
        .crumbs a { text-decoration: none; }
        .crumbs a:hover { text-decoration: underline; }

        .eyebrow {
            font-size: .75rem; letter-spacing: .12em; text-transform: uppercase;
            color: var(--muted); margin: 0 0 .4rem;
        }
        h1 { margin: 0 0 .4rem; letter-spacing: -.01em; }
        .lede { color: var(--muted); margin: 0 0 2rem; }

        .section-title {
            margin: 0 0 1rem; font-size: .78rem; letter-spacing: .1em;
            text-transform: uppercase; color: var(--muted);
        }

        .pattern-card {
            display: flex; align-items: center; gap: 1rem;
            border: 1px solid var(--border); border-radius: 12px;
            background: var(--card); padding: 1.1rem 1.3rem;
            text-decoration: none; color: inherit; margin-bottom: .9rem;
        }
        .pattern-card:hover { border-color: var(--accent); }
        .pattern-card .copy { display: grid; gap: .1rem; flex: 1; }
        .pattern-card .name { font-weight: 650; font-size: 1.05rem; }
        .pattern-card .blurb { color: var(--muted); font-size: .92rem; }
        .pattern-card .go { color: var(--muted); font-size: 1.25rem; }

        .card {
            border: 1px solid var(--border); border-radius: 12px;
            background: var(--card); padding: 1.2rem 1.4rem;
        }
        .card h2 { margin: 0 0 .5rem; font-size: 1.02rem; }
        .card .how { color: var(--muted); font-size: .93rem; margin: 0 0 1.1rem; }
        .card code {
            font-size: .88em; background: var(--chip);
            padding: .08em .35em; border-radius: 5px;
        }
        .issue-head {
            margin: 0 0 1.2rem; padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-soft);
        }
        .issue-head .project { margin: 0 0 .2rem; color: var(--muted); font-size: .8rem; letter-spacing: .08em; text-transform: uppercase; }
        .issue-head h3 { margin: 0 0 .5rem; font-size: 1.15rem; letter-spacing: -.01em; }
        .issue-head .meta { display: flex; flex-wrap: wrap; align-items: center; gap: .45rem; margin: 0; color: var(--muted); font-size: .85rem; }
        button {
            font: inherit; border: 0; border-radius: 8px; cursor: pointer;
            background: var(--accent); color: #fff; padding: .5rem 1.1rem;
        }
        button:hover { background: var(--accent-hover); }
        button[disabled] { opacity: .6; cursor: default; }
        button[disabled]:hover { background: var(--accent); }

        .activity-list { display: grid; gap: 1rem; }
        .activity-event { display: flex; gap: .8rem; align-items: flex-start; }
        /*
         * Standard CSS transition on insertion: @starting-style (CSS
         * Transitions L2) gives newly rendered rows a starting state, so
         * each "Show earlier activity" batch fades and rises in as it is
         * swapped in. Plain transition property — no View Transitions API.
         * Browsers without @starting-style show rows instantly.
         */
        .activity-event {
            transition: opacity 240ms ease-out, transform 240ms ease-out;
        }
        @starting-style {
            .activity-event { opacity: 0; transform: translateY(8px); }
        }
        .avatar {
            width: 30px; height: 30px; flex: none; margin-top: .1rem;
            border-radius: 50%; display: grid; place-items: center;
            color: #fff; font-size: .78rem; font-weight: 650;
            background: #5e6ad2;
        }
        .avatar.c0 { background: #5e6ad2; }
        .avatar.c1 { background: #389e6b; }
        .avatar.c2 { background: #d97706; }
        .avatar.c3 { background: #0f766e; }
        .avatar.c4 { background: #be185d; }
        .activity-content { min-width: 0; flex: 1; display: grid; gap: .3rem; }
        .activity-meta { margin: 0; color: var(--muted); font-size: .92rem; }
        .activity-meta strong { color: var(--fg); font-weight: 650; }
        .activity-content time { white-space: nowrap; }
        .comment-body {
            margin: 0; padding: .55rem .8rem; border-radius: 10px;
            background: var(--row); border: 1px solid var(--border-soft);
        }
        .chip {
            display: inline-block; padding: 0 .55em; border-radius: 999px;
            font-size: .78rem; line-height: 1.7;
            background: var(--chip);
        }
        .activity-empty { color: var(--muted); margin: 0; }
        .load-more { width: 100%; margin-top: .5rem; transition: opacity 150ms ease-out; }
        /*
         * htmx puts .htmx-request on the element driving the request, so the
         * button visibly pulses while the next page is in flight.
         */
        .load-more.htmx-request {
            opacity: .6;
            cursor: progress;
            animation: ctl-pulse 900ms ease-in-out infinite;
        }
        @keyframes ctl-pulse { 50% { opacity: .35; } }
        /*
         * The infinite-scroll sentinel: a row below the newest activity
         * that asks for the next older page once it scrolls into view
         * (hx-trigger="revealed") and swaps itself for it. The spinner is
         * hidden until the request is in flight, when htmx adds
         * .htmx-request to the sentinel.
         */
        .feed-sentinel {
            display: flex; align-items: center; justify-content: center;
            gap: .5rem; padding: .6rem 0 .2rem;
            color: var(--muted); font-size: .92rem;
        }
        .feed-sentinel .sentinel-spinner {
            display: none; width: 14px; height: 14px; flex: none;
            border-radius: 50%; border: 2px solid var(--border);
            border-top-color: var(--accent);
        }
        .feed-sentinel.htmx-request {
            cursor: progress;
            animation: inf-pulse 900ms ease-in-out infinite;
        }
        .feed-sentinel.htmx-request .sentinel-spinner {
            display: inline-block;
            animation: inf-spin 650ms linear infinite;
        }
        @keyframes inf-pulse { 50% { opacity: .4; } }
        @keyframes inf-spin { to { transform: rotate(360deg); } }
        .feed-end {
            margin: .9rem 0 .2rem; text-align: center;
            color: var(--muted); font-size: .85rem;
        }
        /*
         * The lazy-load skeleton: shimmering placeholder rows that stand in
         * for the activity pane while hx-trigger="load" fetches the real
         * log. The skeleton stays close to the loaded rows' height, so the
         * swap causes little of the layout shift the htmx docs warn about.
         */
        .sk-row { display: flex; gap: .8rem; align-items: flex-start; }
        .sk {
            display: block; border-radius: 999px;
            background-color: var(--border-soft);
            background-image: linear-gradient(100deg,
                transparent 20%, var(--sk-shine) 50%, transparent 80%);
            background-size: 200% 100%;
            background-repeat: no-repeat;
            animation: sk-shimmer 1.5s linear infinite;
        }
        .sk-avatar { width: 30px; height: 30px; flex: none; margin-top: .1rem; }
        .sk-copy { flex: 1; min-width: 0; display: grid; gap: .45rem; padding-top: .3rem; }
        .sk-line { height: 13px; }
        .sk-line--meta { width: 40%; }
        .sk-line--body { width: 100%; }
        .sk-row:nth-child(2n) .sk-line--meta { width: 55%; }
        .sk-row:nth-child(2n) .sk-line--body { width: 74%; }
        @keyframes sk-shimmer {
            from { background-position: 200% 0; }
            to { background-position: -200% 0; }
        }
        .visually-hidden {
            position: absolute; width: 1px; height: 1px;
            padding: 0; margin: -1px; overflow: hidden;
            clip: rect(0 0 0 0); white-space: nowrap; border: 0;
        }
        @media (prefers-reduced-motion: reduce) {
            .load-more { animation: none; }
            .activity-event { transition: none; }
            .feed-sentinel, .feed-sentinel .sentinel-spinner { animation: none; }
            .sk { animation: none; }
        }
    </style>
    <x-htmx::scripts />
</head>
<body>
    <header class="site">
        <a class="brand" href="{{ route('home') }}">htmx&nbsp;4 <span>/</span> Laravel</a>
        <div class="site-right">
            <span class="brand-note">imsus/laravel-htmx</span>
            <button
                type="button"
                class="theme-toggle"
                aria-label="Toggle color theme"
                hx-on:click="const root = document.documentElement; const next = root.dataset.theme === 'dark' ? 'light' : 'dark'; root.dataset.theme = next; localStorage.setItem('theme', next);"
            >
                <span class="theme-light">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.9 4.9l1.4 1.4m11.4 11.4 1.4 1.4M2 12h2m16 0h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
                    Dark
                </span>
                <span class="theme-dark">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>
                    Light
                </span>
            </button>
        </div>
    </header>

    <main>
        @yield('content')
    </main>
</body>
</html>
