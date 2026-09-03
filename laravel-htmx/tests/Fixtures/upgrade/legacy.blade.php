<div hx-get="/rows" hx-vars="page: 2" hx-ext="json-enc" hx-inherit="true">
    <button hx-get="/save" hx-target="from:(closest form)">Save</button>
</div>
<script src="https://unpkg.com/htmx.org/dist/ext/json-enc.js"></script>
<script>
    document.body.addEventListener('htmx:xhr:loadend', () => {});
    fetch('/ping').then(r => r.headers.get('HX-Trigger-After-Swap'));
</script>
