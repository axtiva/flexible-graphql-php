Repository knowledge base entrypoint: `index.json`

Rules:
- Read `index.json` first.
- Then read `docs/index/index.json`.
- Then follow `docs/index/navigation.json`.
- Do not broad-scan the repository before consulting index files.
- Use `docs/index/skills-registry.json` to choose relevant skills before loading any full skill content.
- `docs/retro.md` is human-only and must not be used as project knowledge.