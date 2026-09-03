# 02: Response header builder

**What to build:** The server drives swaps from the response: all nine v4 headers (`trigger`, retarget, reswap, reselect, push/replace URL, redirect, location, refresh) through one fluent builder, with the four approved short aliases.

**Blocked by:** 01 (Detection core — needs the manager seam).

**Status:** ready-for-agent

- [ ] All nine headers emitted with correct names and value shapes (`trigger` single header, JSON detail, target key)
- [ ] Removed v4 headers never emitted (no after-swap/after-settle variants)
- [ ] Alias equivalence proven: `target`, `swap`, `push`, `replace` match their originals
- [ ] Headers ignored on 3xx semantics respected where applicable
- [ ] Header-in/header-out Pest tests, no internal-state assertions
