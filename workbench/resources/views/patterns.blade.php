<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>laravel-htmx patterns</title>
    <x-htmx::scripts />
</head>
{{-- Non-form requests (search, delete) carry the token via HX header. --}}
<body hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'>
<h1>Server patterns</h1>
<p><a href="/demo">Back to the fragment demo</a></p>

<h2>Active search</h2>
<p>Type to filter the list. The server returns the <code>results</code> fragment
for partial requests and pushes the query into the URL from the response.</p>

<input
    type="search"
    name="q"
    value="{{ $q }}"
    placeholder="Filter items…"
    autocomplete="off"
    hx-get="/patterns/search"
    hx-trigger="keyup changed delay:300ms"
    hx-target="#results"
    hx-swap="innerHTML"
>

@fragment('results')
<ul id="results">
    @foreach ($items as $item)
        <li>{{ $item }}</li>
    @endforeach
</ul>
@endfragment

<h2>Delete in place</h2>
<p>Each row deletes itself; the server fires a toast event with the response.</p>

<ul id="records">
    @foreach ($items as $item)
        <li>{{ $item }}
            <button
                hx-delete="/patterns/items/{{ urlencode($item) }}"
                hx-target="closest li"
                hx-swap="outerHTML swap:200ms"
            >Delete</button>
        </li>
    @endforeach
</ul>

<div id="toast" role="status"></div>

<h2>Active validation</h2>
<p>Each keystroke validates on the server into the same error slot the
<code>422</code> demo uses — slot survives because the response pins
<code>innerHTML</code>.</p>

<input
    type="text"
    name="name"
    placeholder="At least 3 characters"
    autocomplete="off"
    hx-post="/patterns/validate"
    hx-trigger="keyup changed delay:300ms"
    hx-target="#v-errors"
    hx-swap="innerHTML"
>

<div id="v-errors"></div>

@fragment('v-errors')
@if ($errors->any())
@foreach ($errors->all() as $error)
<p>{{ $error }}</p>
@endforeach
@else
<p>Looks good.</p>
@endif
@endfragment

<script>
    document.body.addEventListener('itemDeleted', (event) => {
        const toast = document.getElementById('toast');
        toast.textContent = event.detail.message;
        setTimeout(() => { toast.textContent = ''; }, 2000);
    });
</script>
</body>
</html>
