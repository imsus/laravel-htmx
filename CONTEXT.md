# Laravel HTMX

Laravel server adapter for htmx 4: detects htmx requests and emits the response headers, fragments, and client config htmx expects.

## Language

**Htmx request**:
Any request carrying the `HX-Request` header.
_Avoid_: Ajax request, partial request, boosted request (boosted is a subtype)

**Partial**:
A fragment-only response returned for an htmx request, without the app layout.
_Avoid_: Snippet, partial view

**Fragment**:
A named Blade section (`@fragment` / `@endfragment`) rendered via `fragment()` / `fragmentIf()`. The mechanism used to produce a `partial`.
_Avoid_: Partial (the wire concept), block

**Full page**:
A complete HTML document returned for non-htmx or history-restore requests.
_Avoid_: Layout, boilerplate

**Trigger**:
A client-side event fired after swap via the single v4 `HX-Trigger` header.
_Avoid_: After-swap trigger, after-settle trigger
