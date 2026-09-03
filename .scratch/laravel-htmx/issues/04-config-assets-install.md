# 04: Config, assets, and install

**What to build:** Strict v4 client defaults plus reproducible client delivery: config with commented 2.x opt-ins, pinned vendored assets with integrity hashes, a scripts component emitting scripts plus the config meta tag, and a one-step install command.

**Blocked by:** 01 (Detection core — shares the provider/config seam).

**Status:** ready-for-agent

- [ ] Strict v4 defaults shipped (explicit inheritance off, minimal swap exclusions, 60s timeout, server re-fetch history) per ADR-0001
- [ ] Pinned `4.0.0` assets vendored with integrity hashes and explicit extension allowlist per ADR-0002
- [ ] Scripts component emits versioned scripts, config meta, and CDN fallback toggle
- [ ] Install command publishes config, views, and assets in one step
- [ ] Config merge, view loading, and published-manifest assertions pass
