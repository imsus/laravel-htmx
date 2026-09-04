<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>laravel-htmx demo</title>
    <x-htmx::scripts />
    @include('partials.styles')
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
