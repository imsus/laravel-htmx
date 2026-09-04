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

    /* htmx request states (class reference for the demo pages). */
    /* Hidden until an ancestor or itself carries .htmx-request. */
    .htmx-indicator { opacity: 0; transition: opacity 200ms ease; }
    .htmx-request .htmx-indicator,
    .htmx-indicator.htmx-request { opacity: 1; }
    /* Trigger dims while its request is in flight. */
    form.htmx-request button { opacity: .55; cursor: progress; }
    /* Targets fade out before the swap lands. */
    #rows.htmx-swapping, #results.htmx-swapping { opacity: 0; transition: opacity 150ms ease; }
    #rows, #results { transition: opacity 150ms ease; }
    /* New content animates in; htmx removes .htmx-added after settle. */
    li.htmx-added { animation: htmx-fade-in 300ms ease-out; }
    @keyframes htmx-fade-in {
        from { opacity: 0; transform: translateY(-4px); }
        to { opacity: 1; transform: none; }
    }
    /* Targets flash while settling, then the class is removed. */
    #rows.htmx-settling, #results.htmx-settling { background: #fff4d6; transition: background 400ms ease; }
    /* Error slots. */
    #form-errors:not(:empty), #v-errors:not(:empty) {
        border: 1px solid #c0392b;
        border-radius: 6px;
        padding: .6rem .8rem;
        background: #fdecea;
    }
    #form-errors p, #v-errors p { color: #96281b; }
    /* Toast. */
    #toast:not(:empty) {
        border: 1px solid #1e7f4f;
        border-radius: 6px;
        padding: .6rem .8rem;
        background: #e6f4ea;
    }
</style>
