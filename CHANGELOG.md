# Changelog

All notable changes to `laravel-shopify` will be documented in this file.

## 1.2.7 - 2026-06-08

Follow-up compatibility release for the Laravel 13 support line.

- Add an explicit Workbench factory PSR-4 mapping so tests pass on case-sensitive Linux runners.
- Keep the Testbench floor fixes from 1.2.6 for Laravel 10 and Laravel 11 CI.
- Verified Composer validation, Pint, and the package Pest suite locally.

## 1.2.6 - 2026-06-08

Follow-up compatibility release for the Laravel 13 support line.

- Raise the Testbench 9 dev dependency floor to avoid the broken Laravel 11/Pest 2 prefer-lowest combination.
- Update the test matrix to exercise Testbench 9.13+ for Laravel 11 and Testbench 8.22+ for Laravel 10.
- Verified local package checks plus clean Laravel 10 and Laravel 11 prefer-lowest installs.

## 1.2.5 - 2026-06-08

Follow-up Laravel compatibility release.

- Include the user migration stub used by the package test harness.
- Keep Pest tests compatible with Pest 2 through 4.
- Allow Laravel 10 CI dependency resolution by permitting Pest 2 dev tooling.
- Guard Laravel's Authenticate::redirectUsing hook for framework versions that support it.
- Commit full Pint formatting expected by CI.

## 1.2.4 - 2026-06-08

Laravel 13 compatibility release.

- Allow Laravel 13, Testbench 11, Pest 4, Larastan 3, and firebase/php-jwt 7 constraints.
- Register the Shopify auth guard directly through Laravel's auth manager.
- Support Laravel's renamed CSRF middleware while retaining older Laravel compatibility.
- Update auth tests for the current guard and repository behavior.

## 1.2.3 - 2025-05-08

**Full Changelog**: https://github.com/cashdash-pro/zaar/compare/1.2.2...1.2.3

## 1.2.2 - 2025-04-20

**Full Changelog**: https://github.com/cashdash-pro/zaar/compare/1.2.1...1.2.2

## 1.2.1 - 2025-04-20

**Full Changelog**: https://github.com/cashdash-pro/zaar/compare/1.2.0...1.2.1

## 1.1.3  - 2025-04-12

Skip db lookups for sessions that are already started

## 1.0.8 - 2025-02-25

octane compatibility

## 1.0.7 - 2025-01-25

Loosen laravel prompts requirements

## 1.0.6 - 2025-01-02

Make the repositories more lenient/nullable when finding

## 1.0.5 - 2025-01-02

Fix ensure sessions

## v0.5.1 - 2024-12-18

**Full Changelog**: https://github.com/cashdash-pro/zaar/compare/v0.5...v0.5.1

## v0.4 - 2024-12-18

**Full Changelog**: https://github.com/cashdash-pro/zaar/compare/v0.3...v0.4

## v0.3 - 2024-12-18

**Full Changelog**: https://github.com/cashdash-pro/zaar/compare/v0.2.2...v0.3

## v0.2.2 - 2024-12-16

**Full Changelog**: https://github.com/cashdash-pro/zaar/compare/v0.2.1...v0.2.2

## v0.2.1 - 2024-12-16

**Full Changelog**: https://github.com/cashdash-pro/zaar/compare/v0.2...v0.2.1

## v0.1 - 2024-12-16

Embedded auth working, external auth is a wip
