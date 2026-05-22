# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
