<style>
    /*!
      chrome-only-reset.css
      No cross-browser normalization. Targets latest stable Chrome/Chromium only.

      With a single rendering engine there's nothing left to "normalize" —
      every remaining rule here is either (a) a Blink-specific default this
      project chooses to override, or (b) a plain opinionated baseline, not
      a cross-browser bug fix. No -moz-, no legacy -webkit- shims that Chrome
      itself doesn't need.
    */

    /* Box sizing & baseline
       ========================================================================== */

    :where(*, *::before, *::after) {
      box-sizing: border-box;
    }

    html {
      line-height: 1.15;
      color-scheme: light dark;
      tab-size: 4;
      -webkit-text-size-adjust: 100%; /* Blink's own font-boosting on narrow/Android viewports — not a Safari patch */
    }

    body {
      margin: 0;
      min-height: 100vh;
    }

    /* Typography
       ========================================================================== */

    a {
      text-decoration-skip-ink: auto;
    }

    code,
    kbd,
    samp,
    pre {
      font-family: ui-monospace, Menlo, Consolas, monospace;
    }

    sub,
    sup {
      font-size: 75%;
      line-height: 0;
      position: relative;
      vertical-align: baseline;
    }

    sub {
      bottom: -0.25em;
    }

    sup {
      top: -0.5em;
    }

    /* Embedded content
       ========================================================================== */

    :where(img, picture, video, canvas, svg) {
      display: block;
      max-width: 100%;
    }

    /* Forms
       ========================================================================== */

    button,
    input,
    optgroup,
    select,
    textarea {
      font: inherit;
      margin: 0;
    }

    /* Chrome ships unprefixed `appearance` (Baseline since March 2022) —
       no -webkit- needed here even though this is Blink. */

    [type="search"] {
      appearance: textfield; /* Chrome/macOS still renders a native rounded search field otherwise */
    }

    /* Standard pseudo-element; Chrome's own ::-webkit-file-upload-button
       is the deprecated form of this now. */

    ::file-selector-button {
      appearance: button;
      font: inherit;
    }

    /* Interaction
       ========================================================================== */

    :focus-visible {
      outline: 2px solid Highlight;
      outline-offset: 2px;
    }

    /* Accessibility
       ========================================================================== */

    @media (prefers-reduced-motion: reduce) {
      :where(*, *::before, *::after) {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
        scroll-behavior: auto !important;
      }
    }

    /* Demo theme. */
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
    button.danger { background: #c0392b; border-color: #c0392b; }
    button.danger:hover { background: #96281b; }
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
