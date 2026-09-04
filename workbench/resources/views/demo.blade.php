<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>laravel-htmx demo</title>
    <x-htmx::scripts />
    <style>
        /* Minimal modern reset (demo only — the package ships no CSS). */
        *, *::before, *::after { box-sizing: border-box; }
        * { margin: 0; }
        body {
            line-height: 1.5;
            font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
            color: #1b2434;
            max-width: 44rem;
            padding: 2rem 1.25rem;
            margin-inline: auto;
        }
        h1, h2, p, ul, form { margin-block: 0 1rem; }
        ul { padding-inline-start: 1.25rem; }
        input, button { font: inherit; }
        input {
            padding: .4rem .6rem;
            border: 1px solid #b9c4d6;
            border-radius: 6px;
        }
        button {
            padding: .4rem .9rem;
            border: 1px solid #2b5fce;
            border-radius: 6px;
            background: #2b5fce;
            color: #fff;
            cursor: pointer;
        }
        button:hover { background: #1e46a0; }
        a { color: #2b5fce; }

        /* htmx request states (class reference for the demo). */
        /* Hidden until an ancestor or itself carries .htmx-request. */
        .htmx-indicator { opacity: 0; transition: opacity 200ms ease; }
        .htmx-request .htmx-indicator,
        .htmx-indicator.htmx-request { opacity: 1; }
        /* Trigger dims while its request is in flight. */
        form.htmx-request button { opacity: .55; cursor: progress; }
        /* Target fades out before the swap lands. */
        #rows.htmx-swapping { opacity: 0; transition: opacity 150ms ease; }
        #rows { transition: opacity 150ms ease; }
        /* New content animates in; htmx removes .htmx-added after settle. */
        #rows li.htmx-added { animation: htmx-fade-in 300ms ease-out; }
        @keyframes htmx-fade-in {
            from { opacity: 0; transform: translateY(-4px); }
            to { opacity: 1; transform: none; }
        }
        /* Target flashes while settling, then the class is removed. */
        #rows.htmx-settling { background: #fff4d6; transition: background 400ms ease; }
        /* Error slot content. */
        #form-errors:not(:empty) {
            border: 1px solid #c0392b;
            border-radius: 6px;
            padding: .6rem .8rem;
            background: #fdecea;
        }
        #form-errors p { color: #96281b; }
    </style>
</head>
<body>
<h1>Grocery list</h1>

<p><a href="/patterns">See the server pattern gallery</a></p>

<p>
    The list below refreshes as a <code>partial</code>. Reload the page or
    restore it from history and the server returns the <code>full page</code>
    instead — same view, same route.
</p>

@fragment('rows')
<ul id="rows">
    @foreach ($items as $item)
        <li>{{ $item }}</li>
    @endforeach
</ul>
@endfragment

<h2>Add an item</h2>

<form hx-post="/items" hx-target="#rows" hx-swap="outerHTML" hx-indicator="#adding">
    @csrf
    <input type="text" name="name" placeholder="At least 3 characters">
    <button type="submit">Add</button>
    <span id="adding" class="htmx-indicator" role="status">Adding…</span>
</form>

<p>Submit a short name to see the <code>422</code> error partial swap into the slot below — the list is untouched.</p>

<div id="form-errors"></div>

@fragment('form-errors')
@foreach ($errors->all() as $error)
<p>{{ $error }}</p>
@endforeach
@endfragment
</body>
</html>
