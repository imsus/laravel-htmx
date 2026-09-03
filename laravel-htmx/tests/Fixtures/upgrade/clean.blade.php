<x-htmx::scripts />
<div hx-get="/rows" hx-target="#rows:inherited" hx-swap="outerHTML">
    <button hx-delete="/items/1" hx-include="closest form">Delete</button>
</div>
<script>
    document.body.addEventListener('htmx:swap', () => {});
</script>
