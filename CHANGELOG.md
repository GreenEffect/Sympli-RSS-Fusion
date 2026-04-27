# Changelog

All notable changes to Sympli RSS Fusion will be documented in this file.

## [1.0.0] - 2026-04-27

### Added

- FR/EN multilingual support through JSON files.
- Configurable UI themes (`default`, `basic`, `dashboard`, `tiles`).
- JSON import/export from `/manage` and the home page.
- OPML import/export support for RSS source lists:
	- `POST /import-master-opml`
	- `GET /export-master-opml?token=...`
	- `GET /manage/{token}/export-opml`
	- `POST /manage/{token}/import-opml`
- RSS source preview with black/star filtering rules.
- Feed deletion from UI.
- Optional auto-pruning for inactive feeds via `.env`.
- Dev mode with detailed errors, logs, and dedicated DB.
- Dedicated 404/500 error pages.
- Personal data page.
- Multilingual footer with institutional links.
- Optional remote version check (GitHub `VERSION`) with "Update available" footer alert.
- OPML parsing safeguards (size limit, URL validation, duplicate URL filtering).
- Dynamic version badge in README header based on GitHub tags.
- Apache front-controller routing via `public/.htaccess` for non-file requests.
- First-install bootstrap now auto-detects the current domain/protocol and injects it into `APP_URL` in generated `.env`.

### Changed

- Rebranding to Sympli RSS Fusion.
- Removed Composer references from the application.
- README header enhanced with centered project logo and key badges (version, PHP minimum, license, status).
- Installation and route documentation updated to include OPML endpoints.
- Reverse-proxy installation behavior improved by honoring forwarded protocol when initializing `APP_URL`.

### Security

- CSRF protection added on destructive and state-changing POST forms (create, update, import, delete).
