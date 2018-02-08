# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [3.1.0](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/compare/3.0.0...3.1.0) - 2018-01-08
### Added
- Added php 7.0 support
- Added twig dependency

### Changed
- Changed internal folder structure to `src`, `tests` and `docs`
- Add support for FQCNs form types
- Switched to templates from SonataAdminBundle
- Replace twig paths with new naming conventions

### Deprecated
- Association templates

### Fixed
- call of render function now Sf3 compatible
- Fix `FormContractor::getDefaultOptions` not checking against form types FQCNs
- Throw an exception if property name is not found in field mappings
- A list field with `actions` type will get all the required field options just like the `_action` field.
- `_action` field will get a proper `actions` type.
- Patched collection form handling script to maintain File input state when new items are added to collections
- Typo in javascript in `edit_mongo_one_association_script.html.twig`
- Check for filter Value in StringFilter
- Missing explicit Symfony dependencies

### Removed
- internal test classes are now excluded from the autoloader
- Support for old versions of Symfony.
