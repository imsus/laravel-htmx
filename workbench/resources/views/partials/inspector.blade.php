{{-- Opt-in htmx attribute inspector (demo only). Toggle outlines every
     element carrying hx-* attributes and exposes them as tooltips. --}}
<button id="hx-inspect-toggle" type="button" aria-pressed="false" title="Highlight htmx attributes">hx*</button>
<style>
    #hx-inspect-toggle {
        position: fixed;
        bottom: 1rem;
        right: 1rem;
        padding: .3rem .7rem;
        border-radius: 999px;
        background: #fff;
        color: #2b5fce;
        border: 1px solid #2b5fce;
    }
    #hx-inspect-toggle[aria-pressed="true"] { background: #2b5fce; color: #fff; }
    body.hx-inspect .hx-node { outline: 2px dashed #7c3aed; outline-offset: 3px; }
</style>
<script>
    (() => {
        const button = document.getElementById('hx-inspect-toggle');
        const seen = new Map();
        button.addEventListener('click', () => {
            const on = document.body.classList.toggle('hx-inspect');
            button.setAttribute('aria-pressed', String(on));
            document.querySelectorAll('*').forEach((el) => {
                const names = el.getAttributeNames().filter((n) => n.startsWith('hx-'));
                if (names.length === 0) return;
                el.classList.toggle('hx-node', on);
                if (on && !seen.has(el)) {
                    seen.set(el, el.getAttribute('title'));
                    el.setAttribute('title', names.map((n) => {
                        const value = el.getAttribute(n);
                        return value ? `${n}="${value}"` : n;
                    }).join('  '));
                } else if (!on && seen.has(el)) {
                    const previous = seen.get(el);
                    if (previous === null) {
                        el.removeAttribute('title');
                    } else {
                        el.setAttribute('title', previous);
                    }
                    seen.delete(el);
                }
            });
        });
    })();
</script>
