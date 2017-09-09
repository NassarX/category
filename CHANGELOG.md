# Rinvex Category Change Log

All notable changes to this project will be documented in this file.

This project adheres to [Semantic Versioning](CONTRIBUTING.md).


## [v2.0.0] - 2017-03-14
- Push it forward, support Laravel 5.4+ and PHPUnit 5.7+
- Apply Laravel 5.4 updates
- Add model meta data docblock
- Add rinvex/cacheable package
- Make database tables configurable
- Add watson/validating package
- Set slug and translatable attributes mutators
- Update validation rules
- Resolve newEloquentBuilder collision in NodeTrait & CacheableEloquent traits
- Fix mutator data casting issues with translatable attributes
- Early auto generate slugs before validation
- Enforce strict type declaration
- Fix stupid gitattributes export-ignore issues

## [v1.0.4] - 2017-01-28
- Explicitly set Laravel version compatibility
- Explicitly set table name

## [v1.0.3] - 2016-12-28
- Fix code style
- Fix documentation typos
- Generate slugs on update
- Fix wrong migration path
- Add required model methods as abstract
- Cascade parent deletions to relationships
- Enforce explicit table and foreign key names

## [v1.0.2] - 2016-12-24
- Remove useless old code
- Fix wrong conditions
- Fix wrong method visibility
- Add find many categories or create if not exists functionality
- Fix wrong installation command
- Refactor without categories scope
- Refactor documentation and add missing nested sets manipulation details

## [v1.0.1] - 2016-12-23
- Fix long categorizable uniqueness index name

## v1.0.0 - 2016-12-23
- Tag first official release.

[v2.0.0]: https://github.com/rinvex/category/compare/v1.0.4...v2.0.0
[v1.0.4]: https://github.com/rinvex/category/compare/v1.0.3...v1.0.4
[v1.0.3]: https://github.com/rinvex/category/compare/v1.0.2...v1.0.3
[v1.0.2]: https://github.com/rinvex/category/compare/v1.0.1...v1.0.2
[v1.0.1]: https://github.com/rinvex/category/compare/v1.0.0...v1.0.1
