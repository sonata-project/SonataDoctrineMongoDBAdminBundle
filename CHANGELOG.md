# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [4.1.1](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/compare/4.1.0...4.1.1) - 2022-04-09
### Fixed
- [[#734](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/734)] The lastpage is correctly set to 1 when there is no results. ([@VincentLanglet](https://github.com/VincentLanglet))

## [4.1.0](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/compare/4.0.1...4.1.0) - 2021-12-04
### Added
- [[#699](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/699)] Added support for Symfony 6 ([@jordisala1991](https://github.com/jordisala1991))

## [4.0.1](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/compare/4.0.0...4.0.1) - 2021-10-01
### Fixed
- [[#680](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/680)] Fixed not forcing to set `field_name` in `DatagridMapper` fields with type defined ([@franmomu](https://github.com/franmomu))
- [[#662](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/662)] Registered `DateRangeFilter` and `DateTimeRangeFilter` in Service Container ([@javer](https://github.com/javer))
- [[#665](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/665)] Allow open ranges for `DateRangeFilter` and `DateTimeRangeFilter` ([@javer](https://github.com/javer))

## [4.0.0](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/compare/4.0.0-rc.1...4.0.0) - 2021-09-06
### Changed
- [[#659](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/659)] Do not set a default route option to `FieldDescription` in `FieldDescriptionFactory` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#641](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/641)] `AbstractDateFilter::getDefaultOptions()` is final ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#641](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/641)] `AbstractDateFilter::getRenderSettings()` is final ([@VincentLanglet](https://github.com/VincentLanglet))

## [4.0.0-rc.1](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/compare/4.0.0-alpha.1...4.0.0-rc.1) - 2021-08-12
### Removed
- [[#632](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/632)] Removed support for PHP < 7.3 ([@franmomu](https://github.com/franmomu))
- [[#632](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/632)] Removed support for Symfony 5.2 ([@franmomu](https://github.com/franmomu))
- [[#603](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/603)] Removed class parameters in the container ([@franmomu](https://github.com/franmomu))

## [4.0.0-alpha.1](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/compare/3.10.0...4.0.0-alpha.1) - 2021-06-13
### Added
- [[#593](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/593)] Added `DateRangeFilter` to be able to filter by dates ([@franmomu](https://github.com/franmomu))
- [[#593](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/593)] Added `DateTimeRangeFilter` to be able to filter by dates with time ([@franmomu](https://github.com/franmomu))

### Changed
- [[#564](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/564)] Changed the guessing type of `FieldDescriptionInterface` with association mapping from `mongo_one` to `FieldDescriptionInterface::MANY_TO_ONE` and from `mongo_many` to `FieldDescriptionInterface::MANY_TO_MANY`. ([@franmomu](https://github.com/franmomu))
- [[#515](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/515)] Add final modifier to classes ([@franmomu](https://github.com/franmomu))
- [[#515](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/515)] Reduce visibility of methods and properties ([@franmomu](https://github.com/franmomu))

### Fixed
- [[#593](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/593)] Fixed using `DateOperatorType::TYPE_EQUAL` type in a `DateFilter` and `DateTimeFilter`, now it filters by the exact date instead of a range. ([@franmomu](https://github.com/franmomu))

### Removed
- [[#593](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/593)] Removed unused `input_type` option in `AbstractDateFilter` ([@franmomu](https://github.com/franmomu))
- [[#593](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/593)] Removed `AbstractDateFilter:: applyTypeIsLessEqual` ([@franmomu](https://github.com/franmomu))
- [[#593](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/593)] Removed `AbstractDateFilter:: applyTypeIsGreaterThan` ([@franmomu](https://github.com/franmomu))
- [[#593](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/593)] Removed `AbstractDateFilter:: applyType` ([@franmomu](https://github.com/franmomu))
- [[#575](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/575)] Remove support for `doctrine/persistence` 1.x ([@franmomu](https://github.com/franmomu))
- [[#357](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/357)] The modelManager getDefaultSortValues does not have default `_sort_by` value anymore. ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#356](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/356)] Special case for the `all` value in ChoiceFilter ([@VincentLanglet](https://github.com/VincentLanglet))

## [3.10.0](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/compare/3.9.0...3.10.0) - 2021-07-20
### Added
- [[#610](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/610)] `global_search` option to the `StringFilter` ([@VincentLanglet](https://github.com/VincentLanglet))

### Deprecated
- [[#599](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/599)] Deprecated `ProxyQuery::getSingleScalarResult()` method ([@franmomu](https://github.com/franmomu))

### Fixed
- [[#607](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/607)] Compatibility with SonataAdminBundle 3.x ([@dmaicher](https://github.com/dmaicher))
- [[#610](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/610)] Stop using `ChoiceTypeFilter` for global search ([@VincentLanglet](https://github.com/VincentLanglet))

## [3.9.0](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/compare/3.8.0...3.9.0) - 2021-05-19
### Added
- [[#589](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/589)] Added `IdFilter` to be able to filter by id in lists ([@franmomu](https://github.com/franmomu))

### Deprecated
- [[#586](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/586)] Deprecated `active_callback` option in `CallbackFilter` ([@franmomu](https://github.com/franmomu))
- [[#586](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/586)] Deprecated not adding `FilterData` as type declaration of argument 4 in the callable passed to `CallbackFilter` ([@franmomu](https://github.com/franmomu))
- [[#584](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/584)] `templates.form` and `templates.filter` config ([@dmaicher](https://github.com/dmaicher))

### Fixed
- [[#584](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/584)] Always merge form and filter templates with existing method calls ([@dmaicher](https://github.com/dmaicher))

## [3.8.0](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/compare/3.7.0...3.8.0) - 2021-04-06
### Added
- [[#553](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/553)] Added `Sonata\DoctrineMongoDBAdminBundle\FieldDescription\FieldDescription` class ([@franmomu](https://github.com/franmomu))
- [[#553](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/553)] Added `Sonata\DoctrineMongoDBAdminBundle\FieldDescription\TypeGuesser` class ([@franmomu](https://github.com/franmomu))
- [[#553](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/553)] Added `Sonata\DoctrineMongoDBAdminBundle\FieldDescription\FilterTypeGuesser` class ([@franmomu](https://github.com/franmomu))
- [[#554](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/554)] Added support for sorting embbeded fields in lists ([@franmomu](https://github.com/franmomu))
- [[#531](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/531)] Added `FieldDescriptionFactory` class to create FieldDescription instances ([@franmomu](https://github.com/franmomu))
- [[#548](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/548)] Added `ModelManager::reverseTransform()` method ([@franmomu](https://github.com/franmomu))
- [[#528](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/528)] Added some `ProxyQueryInterface` to use it as a type declaration ([@franmomu](https://github.com/franmomu))
- [[#526](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/526)] Support for PHP 8.x ([@franmomu](https://github.com/franmomu))

### Deprecated
- [[#558](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/558)] Deprecated `ModelManager::getModelInstance()` method ([@franmomu](https://github.com/franmomu))
- [[#558](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/558)] Deprecated not passing an instance of `ProxyQueryInterface` to `ModelManager::addIdentifiersToQuery()` method ([@franmomu](https://github.com/franmomu))
- [[#558](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/558)] Deprecated not passing an instance of `ProxyQueryInterface` to `ModelManager::batchDelete()` method ([@franmomu](https://github.com/franmomu))
- [[#553](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/553)] Deprecated `Sonata\DoctrineMongoDBAdminBundle\Admin\FieldDescription` class ([@franmomu](https://github.com/franmomu))
- [[#553](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/553)] Deprecated `Sonata\DoctrineMongoDBAdminBundle\Guesser\TypeGuesser` class ([@franmomu](https://github.com/franmomu))
- [[#553](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/553)] Deprecated `Sonata\DoctrineMongoDBAdminBundle\Guesser\FilterTypeGuesser` class ([@franmomu](https://github.com/franmomu))
- [[#531](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/531)] Deprecated `ModelManager::getParentMetadataForProperty()` method ([@franmomu](https://github.com/franmomu))
- [[#531](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/531)] Deprecated `ModelManager::getNewFieldDescriptionInstance()` method ([@franmomu](https://github.com/franmomu))
- [[#531](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/531)] Deprecated `AbstractTypeGuesser` class ([@franmomu](https://github.com/franmomu))
- [[#531](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/531)] Deprecated `FilterTypeGuesser::guessType()` method ([@franmomu](https://github.com/franmomu))
- [[#531](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/531)] Deprecated `TypeGuesser::guessType()` method ([@franmomu](https://github.com/franmomu))
- [[#531](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/531)] Deprecated `ListBuilder::buildActionFieldDescription()` method ([@franmomu](https://github.com/franmomu))
- [[#548](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/548)] Deprecated `ModelManager::modelReverseTransform()` method ([@franmomu](https://github.com/franmomu))
- [[#528](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/528)] Deprecated calling `Filter::apply()` with an instance not implementing `ProxyQueryInterface` ([@franmomu](https://github.com/franmomu))

### Fixed
- [[#549](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/549)] Missing argument 2 in calls to `trigger_error()` ([@franmomu](https://github.com/franmomu))

## [3.7.0](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/compare/3.6.0...3.7.0) - 2021-01-27
### Added
- [[#509](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/509)] Added implementation of `Pager::getCurrentPageResults()` method. ([@franmomu](https://github.com/franmomu))
- [[#501](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/501)] Implemented `Sonata\AdminBundle\Datagrid\PagerInterface::countResults()` ([@dmaicher](https://github.com/dmaicher))

### Deprecated
- [[#509](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/509)] Deprecated `Pager::getResults()` method in favor of `Pager::getCurrentPageResults()`. ([@franmomu](https://github.com/franmomu))
- [[#501](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/501)] `Sonata\DoctrineMongoDBAdminBundle\Datagrid\Pager::computeNbResult()` ([@dmaicher](https://github.com/dmaicher))
- [[#501](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/501)] `Sonata\DoctrineMongoDBAdminBundle\Datagrid\Pager::getNbResults()` ([@dmaicher](https://github.com/dmaicher))
- [[#501](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/501)] `Sonata\DoctrineMongoDBAdminBundle\Datagrid\Pager::setNbResults()` ([@dmaicher](https://github.com/dmaicher))

### Fixed
- [[#502](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/502)] Fixed triggering deprecations from `Pager`. ([@franmomu](https://github.com/franmomu))

## [3.6.0](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/compare/3.5.0...3.6.0) - 2021-01-04
### Added
- [[#486](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/486)] Added `DataSource` to provide a `DataSourceInterface` implementation. ([@franmomu](https://github.com/franmomu))

### Deprecated
- [[#492](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/492)] Deprecated `ModelManager::getMetadata()` method. ([@franmomu](https://github.com/franmomu))
- [[#492](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/492)] Deprecated `ModelManager::hasMetadata()` method. ([@franmomu](https://github.com/franmomu))
- [[#486](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/486)] Deprecated `ModelManager::getDataSourceIterator()`. ([@franmomu](https://github.com/franmomu))
- [[#473](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/473)] Deprecated `ModelManager::getModelIdentifier()`. ([@franmomu](https://github.com/franmomu))
- [[#473](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/473)] Deprecated `ModelManager::getDefaultSortValues()`. ([@franmomu](https://github.com/franmomu))
- [[#473](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/473)] Deprecated `ModelManager::getDefaultPerPageOptions()`. ([@franmomu](https://github.com/franmomu))
- [[#473](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/473)] Deprecated `ModelManager::modelTransform()`. ([@franmomu](https://github.com/franmomu))
- [[#451](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/451)] Deprecated passing `null` as argument 2 for `ModelManager::find()`; ([@franmomu](https://github.com/franmomu))
- [[#451](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/451)] Deprecated passing `null` as argument 1 for `ModelManager::getNormalizedIdentifier()`; ([@franmomu](https://github.com/franmomu))
- [[#451](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/451)] Deprecated passing other type than `object` as argument 1 for `ModelManager::getUrlSafeIdentifier()`; ([@franmomu](https://github.com/franmomu))

### Fixed
- [[#497](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/497)] Fixed calling to deprecated `Pager::setCountColumn()` method. ([@franmomu](https://github.com/franmomu))
- [[#479](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/479)] Fixed calling to `AdminInterface::id` without an object. ([@franmomu](https://github.com/franmomu))
- [[#463](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/463)] `FormContractor::getDefaultOptions()` passes `collection_by_reference` option instead of `by_reference` to `AdminType` in order to respect the new API ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#470](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/470)] Fixed returning an array of identifiers in `ModelManager::getIdentifierFieldNames`. ([@franmomu](https://github.com/franmomu))
- [[#468](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/468)] Fixed the return type of `TypeGuesser::guessType`, it must return `null` or `TypeGuess`. ([@franmomu](https://github.com/franmomu))
- [[#459](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/459)] Fixed deprecation constructing `FieldDescription` without arguments. ([@franmomu](https://github.com/franmomu))

## [3.5.0](sonata-project/SonataDoctrineMongoDBAdminBundle/compare/3.4.0...3.5.0) - 2020-10-09
### Added
- [[#438](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/438)] `FormContractor::getDefaultOptions()` pass `by_reference` from `CollectionType` to `AdminType` ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#430](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/430)] Added `ModelManager::supportsQuery()` ([@franmomu](https://github.com/franmomu))

### Changed
- [[#437](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/437)] Mark some classes as final ([@franmomu](https://github.com/franmomu))

### Deprecated
- [[#430](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/430)] Deprecated `ProxyQuery::getUniqueParameterId()` and `ProxyQuery::entityJoin()` ([@franmomu](https://github.com/franmomu))
- [[#430](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/430)] Deprecated calling `ModelManager::executeQuery()` with anything but an instance of `Doctrine\ODM\MongoDB\Query\Builder` or `Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery` ([@franmomu](https://github.com/franmomu))

## [3.4.0](https://github.com/SonataDoctrineMongoDBAdminBundle/compare/3.3.0...3.4.0) - 2020-09-24
### Added
- [[#353](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/353)] Allow `_sort_by` filter to not be initially defined ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#420](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/420)] Added returning the own instance in `ProxyQuery` based on PHPDoc of `ProxyQueryInterface` ([@franmomu](https://github.com/franmomu))
- [[#408](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/408)] Added support for symfony/options-resolver:^5.1 ([@franmomu](https://github.com/franmomu))
- [[#408](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/408)] Added support for twig 3 ([@franmomu](https://github.com/franmomu))
- [[#409](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/409)] Added doctrine/persistence as a dependency ([@franmomu](https://github.com/franmomu))
- [[#379](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/379)] Added `ModelManager::getDefaultPerPageOptions()` ([@franmomu](https://github.com/franmomu))
- [[#379](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/379)] Added `FieldDescription::getTargetModel()` ([@franmomu](https://github.com/franmomu))
- [[#378](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/378)] Added `doctrine/collections` dependency ([@franmomu](https://github.com/franmomu))
- [[#375](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/375)] Added `sonata-project/form-extensions` dependency ([@franmomu](https://github.com/franmomu))
- [[#346](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/346)] "sonata.admin.manager" tag to "sonata.admin.manager.doctrine_mongodb" service ([@phansys](https://github.com/phansys))

### Changed
- [[#380](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/380)] Changed `DatagridBuilder` constructor's first parameter typehint from `Symfony\Component\Form\FormFactory` to `Symfony\Component\Form\FormFactoryInterface` ([@franmomu](https://github.com/franmomu))
- [[#373](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/373)] Use `deprecated` tag instead of `sonata_template_deprecate` to not throw unwanted deprecation notices ([@franmomu](https://github.com/franmomu))
- [[#373](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/373)] Bump Twig to 2.6 ([@franmomu](https://github.com/franmomu))
- [[#374](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/374)] Bump Symfony to 4.4 ([@franmomu](https://github.com/franmomu))

### Deprecated
- [[#414](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/414)] Deprecate creating `ObjectAclManipulator` without passing a `ManagerRegistry` object ([@franmomu](https://github.com/franmomu))
- [[#415](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/415)] Deprecate `ModelManager::getModelCollectionInstance()` ([@franmomu](https://github.com/franmomu))
- [[#415](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/415)] Deprecate `ModelManager::collectionClear()` ([@franmomu](https://github.com/franmomu))
- [[#415](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/415)] Deprecate `ModelManager::collectionHasElement()` ([@franmomu](https://github.com/franmomu))
- [[#415](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/415)] Deprecate `ModelManager::collectionAddElement()` ([@franmomu](https://github.com/franmomu))
- [[#415](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/415)] Deprecate `ModelManager::collectionRemoveElement()` ([@franmomu](https://github.com/franmomu))
- [[#415](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/415)] Deprecate `ModelManager::getPaginationParameters()` ([@franmomu](https://github.com/franmomu))
- [[#415](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/415)] Deprecate `ModelManager::getSortParameters()` ([@franmomu](https://github.com/franmomu))
- [[#384](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/384)] Deprecate `ModelManager::getParentFieldDescription()` ([@franmomu](https://github.com/franmomu))
- [[#381](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/381)] Deprecated passing arguments to`ProxyQuery::execute()` ([@franmomu](https://github.com/franmomu))
- [[#368](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/368)] Deprecated `AbstractDateFilter::typeRequiresValue` ([@franmomu](https://github.com/franmomu))
- [[#368](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/368)] Deprecated `ModelManager::camelize` ([@franmomu](https://github.com/franmomu))
- [[#368](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/368)] Deprecated constructing `ModelManager` without passing an instance of `PropertyAccessorInterface` as second argument ([@franmomu](https://github.com/franmomu))
- [[#379](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/379)] Deprecated `FieldDescription::getTargetEntity()` in favor of `FieldDescription::getTargetModel()` ([@franmomu](https://github.com/franmomu))

### Fixed
- [[#385](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/385)] Fixed exception captured when creating `ObjectId` ([@franmomu](https://github.com/franmomu))
- [[#377](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/377)] Replace deprecated operator constants ([@franmomu](https://github.com/franmomu))
- [[#372](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/372)] Fixed passing `null` to `Doctrine\ODM\MongoDB\Query\Builder::skip` and `Doctrine\ODM\MongoDB\Query\Builder::limit` ([@franmomu](https://github.com/franmomu))
- [[#359](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/359)] Fixed returning `void` from methods which are intended to return values and returning values from methods which are intended to return `void`; ([@phansys](https://github.com/phansys))
- [[#359](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/359)] Fixed weak check at `ModelManager::getNormalizedIdentifier()` ([@phansys](https://github.com/phansys))

### Removed
- [[#358](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/358)] Drop support of php 7.1 ([@VincentLanglet](https://github.com/VincentLanglet))
- [[#424](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/424)] Remove support for `doctrine/mongodb-odm` <2.0 ([@franmomu](https://github.com/franmomu))
- [[#409](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/409)] Removed deprecations coming from doctrine/common namespace ([@franmomu](https://github.com/franmomu))
- [[#378](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/378)] Removed `twig/extensions` dependency ([@franmomu](https://github.com/franmomu))
- [[#378](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/378)] Removed `jmikola/geojson` dependency ([@franmomu](https://github.com/franmomu))
- [[#375](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/pull/375)] Removed SonataCoreBundle dependency ([@franmomu](https://github.com/franmomu))

## [3.3.0](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/compare/3.2.2...3.3.0) - 2020-01-08
### Fixed
- Fixed filtering by a field of an embedded object
- Fixed `_action` item in ListMapper when type is `null`
- Fixed support for doctrine/mongodb-odm 2.x

### Removed
- Support for Symfony < 3.4
- Support for Symfony >= 4, < 4.2

## [3.2.2](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/compare/3.2.1...3.2.2) - 2019-10-16
### Added
- support for `doctrine/mongodb-odm` 2.x and `doctrine/mongodb-odm-bundle` 4.x

## [3.2.1](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/compare/3.2.0...3.2.1) - 2019-07-03
### Fixed
- Use proper namespace for `Sonata\Exporter\Source\DoctrineODMQuerySourceIterator`

## [3.2.0](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/compare/3.1.1...3.2.0) - 2019-04-03
### Fixed
- using the new collection type namespace
- deprecation for symfony/config 4.2+
- missing association admin class in datagrid filters.
- `isLastPage`() always returning `false`

### Removed
- support for php 5 and php 7.0

## [3.1.1](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/compare/3.1.0...3.1.1) - 2018-02-08
### Changed
- All templates references are updated to twig namespaced syntax

### Fixed
- Compatibility with autocomplete form type
- FQCN for form types (Symfony 3 compatibility)
- Association field popup

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
