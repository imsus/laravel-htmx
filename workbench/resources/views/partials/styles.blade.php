<style>
    /*!
     * modern-normalize (Chrome-only build)
     * Adapted from normalize.css v8.0.1 | MIT License | github.com/necolas/normalize.css
     *
     * Rewritten for latest Chrome using:
     *   - @layer, to keep the reset low-priority in the cascade
     *   - :where(), to keep every selector at 0 specificity (trivial to override)
     *   - native CSS nesting
     *   - logical properties instead of physical ones
     *
     * Dropped entirely (IE/Firefox/old-Safari-only bug fixes with no effect in Chrome):
     *   - -moz-focus-inner / -moz-focusring        (Firefox only)
     *   - fieldset's Firefox padding correction
     *   - abbr[title] border-bottom reset          (Chrome ≤56 bug)
     *   - a { background-color: transparent }      (IE10-only tap-highlight bug)
     *   - button/select text-transform reset       (Edge/Firefox inheritance bug)
     *   - [type="number"] spin-button height fix   (no-op in current Chrome)
     *   - all vendor prefixes Chrome no longer needs (-webkit-text-size-adjust,
     *     -webkit-appearance → unprefixed since Chrome 84+)
     *
     * Kept (still meaningful, since Chrome is WebKit/Blink-based):
     *   - ::-webkit-search-decoration / ::-webkit-file-upload-button
     */

    @layer reset {
      :where(html) {
        line-height: 1.15;
        text-size-adjust: 100%; /* prefix no longer required in Chrome */
      }

      :where(body) {
        margin: 0;
      }

      :where(main) {
        display: block;
      }

      :where(h1) {
        font-size: 2em;
        margin-block: 0.67em;
      }

      :where(hr) {
        box-sizing: content-box;
        block-size: 0;
        overflow: visible;
      }

      :where(pre, code, kbd, samp) {
        font-family: ui-monospace, monospace;
        font-size: 1em;
      }

      :where(abbr[title]) {
        text-decoration: underline dotted;
      }

      :where(b, strong) {
        font-weight: bolder;
      }

      :where(small) {
        font-size: 80%;
      }

      :where(sub, sup) {
        position: relative;
        font-size: 75%;
        line-height: 0;
        vertical-align: baseline;

        &:where(sub) {
          bottom: -0.25em;
        }

        &:where(sup) {
          top: -0.5em;
        }
      }

      :where(img) {
        border-style: none;
      }

      :where(button, input, optgroup, select, textarea) {
        font: inherit;
        margin: 0;
      }

      :where(button, input) {
        overflow: visible;
      }

      :where(button, [type="button"], [type="reset"], [type="submit"]) {
        appearance: button; /* unprefixed in Chrome */
      }

      :where(legend) {
        box-sizing: border-box;
        display: table;
        max-inline-size: 100%;
        padding: 0;
        color: inherit;
        white-space: normal;
      }

      :where(progress) {
        vertical-align: baseline;
      }

      :where(textarea) {
        overflow: auto;
      }

      :where([type="checkbox"], [type="radio"]) {
        box-sizing: border-box;
        padding: 0;
      }

      :where([type="search"]) {
        outline-offset: -2px;

        &::-webkit-search-decoration {
          -webkit-appearance: none; /* still Chrome/WebKit-specific, no unprefixed equivalent */
        }
      }

      ::-webkit-file-upload-button {
        font: inherit;
        appearance: button;
      }

      :where(details) {
        display: block;
      }

      :where(summary) {
        display: list-item;
      }

      :where(template, [hidden]) {
        display: none;
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
