# Strict htmx 4 defaults

Ship htmx 4 semantics as-is: `implicitInheritance=false`, `noSwap=[204,304]`, `defaultTimeout=60000`, history re-fetch. Do not add 2.x compatibility shims (`implicitInheritance=true`, `noSwap` with `4xx/5xx`).

2.x-compat hides the three breaking changes every app must confront (explicit `:inherited`, error responses now swap, history re-fetch). Shims defer breakage and double the documented behavior. Strict defaults plus the `htmx:upgrade-check` command force one correct migration; compat values stay in `config/htmx.php` as commented opt-ins.
