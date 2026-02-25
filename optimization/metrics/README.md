# Metrics Toolkit

## Lighthouse CI
- Install: `npm i -g @lhci/cli`
- Configure URLs in `lighthouserc.json`
- Run: `./scripts/run_lhci.sh https://yoursite.example`

## GTmetrix (optional)
- Get an API key and set email/key in `scripts/gtmetrix.sh`
- Run: `./scripts/gtmetrix.sh https://yoursite.example`

Tips:
- Schedule weekly in CI and track trends over time.