# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 0.23.1

### Fixed
- Admin settings page hung at "Loading …" on Nextcloud 33
  (`TypeError: r.querySelector is not a function`). NC33 reimplemented the
  legacy `OCP.AppConfig` JS API on top of axios: its success callback used
  to hand over an XML document and now hands over a parsed object, so the
  page's XML parsing blew up. The page no longer uses that API — it reads
  and writes app config through the new admin-only endpoints
  `GET`/`PUT /apps/fairmeeting/api/admin/settings`, which also collapses
  the ~20 requests per page load and per save into a single one.
- A failing settings request now shows an error instead of leaving the
  page stuck on the loading placeholder.

### Changed
- `max-version` raised to Nextcloud 33.

## 0.23.0

### Added
- Calendar invitation links now route through a new
  `/apps/fairmeeting/j/{room}` endpoint that resolves the per-creator
  server URL, signs a Jitsi JWT, and 303-redirects to the meeting with
  `userInfo.displayName` already filled in.
- Calendar listener creates a `Room` row for each injected link; the join
  endpoint 404s when no row matches, blocking JWTs for guessed names.
- Calendar listener cleans up its `Room` rows when the corresponding
  event is deleted or moved to the calendar trash. Calendar-sourced rooms
  appear in the room list with a `Calendar` badge.
- New **Personal settings → fairmeeting** section: every user can paste
  their own long-lived JWT token (fetched from a Keycloak-backed token
  service) so they appear in meetings under their own identity. Token
  validity (`exp` claim) is decoded client-side and shown next to the
  field. The page also shows a banner with the effective server the
  user's meetings host on and why.
- New admin setting *JWT Token Service URL*, surfaced as an
  *Open token service* button in each user's Personal settings.
- New admin settings *Pro Server URL*, *Pro Group Name* (default
  `fairmeeting`) and *Pro Server Badge Label*. Meetings created by users
  in that Nextcloud group route to the pro server; everyone else uses
  the default server. The NC group typically mirrors a Keycloak group
  via OIDC group sync. The check runs on every join, so group changes
  take effect immediately. The configured badge label appears next to
  the room hostname wherever the room is displayed.
- Per-room *Skip prejoin* / *Disable mobile app prompt* settings are now
  tri-state (`Use admin default` / `Always` / `Never`).
- Room list now shows hostname, server badge and an activity label
  ("last joined X ago" or "created Y ago"), backed by new `created_at`
  and `last_joined_at` columns. Toolbar adds a live search box and a
  sort dropdown (`Recent activity`, `Name`, `Server`).
- Pre-join page redesigned: server banner at the top, settings grouped
  into "Your settings" and "Room settings (creator only)" cards with
  *Media defaults* and *Join experience* sub-sections, a prominent
  primary Join button, a "Share this meeting" card with
  copy-to-clipboard, and icons throughout.
- Loading skeleton on the room list, redesigned empty state, and a
  confirmation prompt on the delete-room action.
- ~60 new German translations covering all the strings added in this
  release.

### Changed
- Default room list sort is now most-recent activity.

### Removed
- The admin-wide *JWT Token* field. A single token shared by all users
  of an instance always resulted in every user appearing in the meeting
  with the admin's identity. The new priority order is:
  1. The user's own personal JWT token (Personal settings).
  2. NC HS256-signs a fresh token from the configured *JWT Secret*, with
     the session user's display name in the `context.user.name` claim.
  3. No token is sent — fairmeeting handles auth on its own.

  **Upgrade action:** sites that previously relied on the admin's manual
  token must do one of the following before upgrading, otherwise users
  hit fairmeeting without a JWT and joins may fail:
  - Configure *JWT Secret* + *JWT App ID* (NC will sign per-user tokens
    locally, no further user action needed), or
  - Have each user paste their personal long-lived token under
    Personal settings → fairmeeting.

  The legacy `jwt_token` app-config row is left in place during upgrade
  but is no longer read. It can be removed with
  `occ config:app:delete fairmeeting jwt_token`.

### Fixed
- "Join in new tab" URL builder now puts Jitsi `config.*` / `userInfo.*`
  overrides in the `#` hash (was the `?` query, which Jitsi silently
  ignores).
- `this.n is not a function` TypeError on the room list (plural
  translation helper was missing from the AppGlobal mixin).

## 0.22.9

### Added
- Two new meeting URL options, configurable both as admin defaults and as
  per-room overrides:
  - "Skip prejoin page" — joins the meeting directly, bypassing Jitsi's
    audio/video test screen.
  - "Disable mobile app prompt" — opens the meeting in the browser instead
    of nagging the user to install the Jitsi mobile app.
  Admins set defaults in the fairmeeting settings page; room creators can
  override per room on the meeting page. Calendar-generated links use the
  admin default (calendar invitations have no per-room context).

### Fixed
- The "open in new tab" / new-room meeting URL now puts Jitsi config
  overrides (`config.*`, `userInfo.*`, `interfaceConfig.*`, `devices.*`)
  into the URL hash fragment instead of the query string. Jitsi's web client
  only honors these from the hash, which is why the prejoin/deep-linking
  options were previously ignored when the same flags worked on calendar
  links. The auth `jwt` parameter stays in the query string.

### Changed
- Listener now writes the calendar object back synchronously (no behavior
  change since 0.22.8 — this is a cleanup of the rewrite).
- Calendar listener log noise demoted: routine "checking event" / "no
  modifications needed" lines are now `debug` level; only the actual
  link-injection event stays at `info`.
- Removed dead code: unused `OCP\Calendar\IManager` injection in the
  calendar listener constructor.
- Root-level cleanup: deleted broken/duplicate `package-lock.json`,
  `.editorconfig`, `.eslintignore`, `.php-cs-fixer.dist.php`, and
  `.onedev-buildspec.yml`. The real config files live inside `fairmeeting/`.

## 0.22.8

### Fixed
- Saving a calendar event no longer blocks the response for ~3 seconds.
  The listener used to defer its work into a `register_shutdown_function`
  with a hard-coded `sleep(3)`, which under Apache/PHP-FPM kept the HTTP
  connection open until the callback returned. The listener now processes
  the event inline; the self-triggered UpdatedEvent for the second write
  short-circuits in `shouldAddFairmeeting()` (URL already present), so
  there is no loop.

## 0.22.7

### Fixed
- Calendar integration now works on Nextcloud 32. The calendar event listener
  was registered against the legacy `OCA\DAV\Events\CalendarObject{Created,Updated}Event`
  classes, which were moved to `OCP\Calendar\Events\` in NC32. The listener is
  now registered for both namespaces, so meetings created with attendees or
  longer than the configured minimum duration receive a fairmeeting link
  on NC32 as well.
- Replaced deprecated `OCP\ILogger` with `Psr\Log\LoggerInterface` in the
  calendar event listener.

## 0.22.6

### Fixed
- Room names with slashes are now sanitized (replaced with underscores) to prevent Jitsi tenant issues

### Changed
- Updated README documentation to reflect keyword-based calendar features

## 0.22.5

### Added
- Calendar integration for event editing
- Flexible keyword replacement in location and/or description fields
- New admin checkboxes to control keyword replacement locations
- Nextcloud 32 support

### Changed
- Removed "Also add to event description" option
- Removed description text template
- Simplified non-keyword mode: only fills empty location fields
- Moved keyword triggers to bottom of admin interface
- Migrated from deprecated `getContentSecurityPolicyManager()` to `\OC::$server->get()` for Nextcloud 32 compatibility

### Fixed
- Keyword detection now searches all fields but only replaces in selected ones
- Fixed hanging issue when non-keyword mode encounters existing location content
- Improved reliability for both event creation and editing

## 0.22.3

### Fixed

- Added room name prefix functionality with linting and PHP analysis
- Fixed Composer autoloader conflict with Jitsi app by adding author to composer.json (#9)

### Added

- Calendar integration functionality
- Token service Content Security Policy (CSP) configuration
- Fair use conditions documentation
- New conference screenshot (fairmeeting_conf.png)

## 0.21.0

### Added

- use fairmeeting
- Option to open meetings in new tab and minimal UI
- Support for JWT token in adminsettings

## 0.20.0

### Added

- Option to open meetings in new tab and minimal UI
- Support for JWT token in adminsettings

## 0.19.0

### Added

- Nextcloud 29 support
- Nextcloud 30 support
- Nextcloud 31 support, please report any issues

## 0.18.0

### Added

- Nextcloud 27 support
- Nextcloud 28 support

### Changed

- Translations update

## 0.17.0

### Fixed

- Not all rooms visible in long room list
- Room list button tooltips ([#22](https://github.com/nextcloud/jitsi/issues/22))
- Translation of „Copy to clipboard“ button tooltip ([#23](https://github.com/nextcloud/jitsi/pull/23))

### Changed

- Updated translations

## 0.16.1

### Fixed

- PHP 7.4 compatibility

## 0.16.0

### Changed

- Nextcloud 25 support
- Improved „join with app“ buttons
- Updated translations

## 0.15.0

### Added

- Greek translation

### Fixed

- Browser version 100 support

### Changed

- Nextcloud code-style applied
