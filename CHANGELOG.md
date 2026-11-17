# Changelog

All notable changes to `ag-grid-laravel` will be documented in this file.

## Unreleased

- **Security:** Refuse filter, sort and set value columns that are hidden on their model (`$hidden`/`$visible`), including paths through hidden relations. Previously any client could filter on columns like `password` and read them through the row count.
- **Security:** Quote json paths in set filters. A crafted `filterModel` key could previously inject SQL.
- **Security:** Exports only include columns from `getAgGridColumnDefinitions()`; requested columns without a definition are dropped instead of being read from the model.
- Add allowed columns for filters and sorts: pass them to `forRequest()`, `forSetValuesRequest()`, `forSelection()` or the constructor, or implement `AgGridFilterable` on the model. Columns outside the list throw `UnauthorizedColumn`.
- Add the `require_allowed_columns` config option to refuse every column of grids without allowed columns. It defaults to `false` and becomes `true` in the next major version.

## 0.5.0 (2026-08-07)

- Add support for PHP 8.5
- Upgrade Laravel Excel to 4.x-dev (needed for PHP 8.5 support)
- Remove support for Laravel 11 and below
- Remove support for PHP 8.2 and below

## 0.4.0 (2026-05-27)

- Added support for Laravel 13
- Secured GitHub Actions
- Removed unused Actions

## 0.3.0 (2025-11-05)

- Added support for Laravel 11 & 12
- Added support for nested relations in filters
- Added support for set values handling

## 0.2.1 (2023-10-5)

- Fixed double query for resource classes

## 0.2.0 (2023-08-24)

- Rename `$params` to `$filters` in `AgGridCustomFilterable`

## 0.1.0 (2023-08-24)

- Initial Release
