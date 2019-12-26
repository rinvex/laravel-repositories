# Rinvex Repository Change Log

All notable changes to this project will be documented in this file.

This project adheres to [Semantic Versioning](CONTRIBUTING.md).


## [v5.0.1] - 2019-09-24
- Add missing laravel/helpers composer package

## [v5.0.0] - 2019-09-23
- Upgrade to Laravel v6 and update dependencies

## [v4.1.0] - 2019-06-02
- Update composer deps
- Drop PHP 7.1 travis test
- Fix deprecated fire => dispatch method

## [v4.0.0] - 2019-03-03
- Rename environment variable QUEUE_DRIVER to QUEUE_CONNECTION
- Require PHP 7.2 & Laravel 5.8
- Apply PHPUnit 8 updates
- Replace __ CLASS __ & get_called_class() with self::class & static::class (potentially deprecated in PHP 7.4)
- Laravel 5.8 changed cache TTL to use seconds not minutes

## [v3.0.1] - 2018-12-22
- Add Laravel v5.7 support
- Update composer dependencies
- Add PHP 7.3 support to travis

## [v3.0.0] - 2018-10-01
- Enforce Consistency
- Support Laravel 5.7+
- Rename package to rinvex/laravel-repositories

## [v2.0.3] - 2017-01-27
- Revert "Add support for Laravel 5.4" (v2.x won't get Laravel 5.4 support #131)

## [v2.0.2] - 2017-01-27
- Add support for Laravel 5.4

## [v2.0.1] - 2016-08-06
- Add missing argument to simplePaginate method (#54)
- Add config option for default model directory (close #55)
- Extract Cache methods to a Contract / Trait (close #57)
- Fix spelling typos & fix docs
- Added extra logic to paginate and simplePaginate methods, fixes #61 (#62)

## [v2.0.0] - 2016-07-01
- Drop `findOrCreate` method (close #33)
- Change `retrieveModel` behavior (close #34)
  - Separate functionality into `setModel`, `getModel`, and `createModel`
  - `createModel` always return a new clean model instance
- Drop `enableCache` & `isCacheEnabled` (close #35)
  - These methods have duplicate functionality of `setCacheLifetime` & `getCacheLifetime`
- Add filtration through `where`, 'whereIn', `whereNotIn` methods
- Add `offset`, and `limit` functionality to the query
- Drop `addGlobalScope`, `withoutGlobalScopes` methods (close #8)
- Move `$with` argument to setters & getters (close #36)
- Rename `$column` to `$attribute` for more naming abstraction (close #37)
- Add `where`, `whereIn`, `whereNotIn` methods for flexible filtration (close #6)
- Update `findWhere`, `findWhereIn`, `findWhereNotIn` methods to utilize the new filtration (close #38)
- Centralize & enforce filtration and data access rules (close #39)
- Add `offset` & `limit` functionality to queries (close #40)
- Add `simplePaginate` method for light weight pagination (close #41)
- Refactor callback execution, clean code, and reset query conditions after execution (close #30, close #42)

## [v1.0.5] - 2016-06-27
- Fix clear cache on update issue (close #27)
- Move cache lifetime & driver args to setter methods (close #26)
- Review & rewrite documentation to reflect recent updates

## [v1.0.4] - 2016-06-24
- Add Laravel 5.3.* support
- Update chat link
- Review and enhance documentation from scratch
  - Add gif for quick example workflow
  - Add visual graphics for better attraction & understanding
  - Enhance the whole documentation framework and outlines
  - Add quick example section & isolate the advanced details
  - Add example for Coding To An Interface
- Fix fired event names typo (close #25)
- Update contributing guidelines

## [v1.0.3] - 2016-06-22
- Fix wrong RepositoryServiceProvider PSR-4 namespace (close #19)

## [v1.0.2] - 2016-06-22
- Fix `findWhere` wrong results and fix docs mistakes (close #15)
- Enable/disable cache per query (close #16)
- Revamp the entire documentation (close #17)

## [v1.0.1] - 2016-06-21
- Update docs, docblocks, and fix homepage link
- Add per repository cache lifetime/driver support (Close #10)

## v1.0.0 - 2016-06-18
- Tag first release

[v5.0.1]: https://github.com/rinvex/laravel-repositories/compare/v5.0.0...v5.0.1
[v5.0.0]: https://github.com/rinvex/laravel-repositories/compare/v4.1.0...v5.0.0
[v4.1.0]: https://github.com/rinvex/laravel-repositories/compare/v4.0.0...v4.1.0
[v4.0.0]: https://github.com/rinvex/laravel-repositories/compare/v3.0.1...v4.0.0
[v3.0.1]: https://github.com/rinvex/laravel-repositories/compare/v3.0.0...v3.0.1
[v3.0.0]: https://github.com/rinvex/laravel-repositories/compare/v2.0.3...v3.0.0
[v2.0.3]: https://github.com/rinvex/laravel-repositories/compare/v2.0.2...v2.0.3
[v2.0.2]: https://github.com/rinvex/laravel-repositories/compare/v2.0.1...v2.0.2
[v2.0.1]: https://github.com/rinvex/laravel-repositories/compare/v2.0.0...v2.0.1
[v2.0.0]: https://github.com/rinvex/laravel-repositories/compare/v1.0.5...v2.0.0
[v1.0.5]: https://github.com/rinvex/laravel-repositories/compare/v1.0.4...v1.0.5
[v1.0.4]: https://github.com/rinvex/laravel-repositories/compare/v1.0.3...v1.0.4
[v1.0.3]: https://github.com/rinvex/laravel-repositories/compare/v1.0.2...v1.0.3
[v1.0.2]: https://github.com/rinvex/laravel-repositories/compare/v1.0.1...v1.0.2
[v1.0.1]: https://github.com/rinvex/laravel-repositories/compare/v1.0.0...v1.0.1
