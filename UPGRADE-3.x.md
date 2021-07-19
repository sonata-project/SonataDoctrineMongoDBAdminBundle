UPGRADE 3.x
===========

UPGRADE FROM 3.9 to 3.10
========================

### Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery

Deprecated `getSingleScalarResult()` method.

UPGRADE FROM 3.8 to 3.9
=======================

### Sonata\DoctrineMongoDBAdminBundle\Filter\CallbackFilter

Deprecated not returning a boolean from the `callback` option.
Deprecated `active_callback` option, you MUST return a boolean in the `callback` option to specify if the filter should
be active or not.

Not adding `Sonata\AdminBundle\Filter\Model\FilterData` as type declaration of argument 4 of the callable passed to
`Sonata\DoctrineMongoDBAdminBundle\Filter\CallbackFilter` is deprecated. In version 4.0 this argument will be an
instance of `Sonata\AdminBundle\Filter\Model\FilterData`.

UPGRADE FROM 3.7 to 3.8
=======================

### Sonata\DoctrineMongoDBAdminBundle\Admin\FieldDescription

Deprecated this class in favor of `Sonata\DoctrineMongoDBAdminBundle\FieldDescription\FieldDescription`.

### Sonata\DoctrineMongoDBAdminBundle\Guesser\FilterTypeGuesser

Deprecated this class in favor of `Sonata\DoctrineMongoDBAdminBundle\FieldDescription\FilterTypeGuesser`.

### Sonata\DoctrineMongoDBAdminBundle\Guesser\TypeGuesser

Deprecated this class in favor of `Sonata\DoctrineMongoDBAdminBundle\FieldDescription\TypeGuesser`.

### Sonata\DoctrineMongoDBAdminBundle\Model\ModelManager

Deprecated `modelReverseTransform()` method, use `reverseTransform()` instead.

### Sonata\DoctrineMongoDBAdminBundle\Builder\ListBuilder

Deprecated `buildActionFieldDescription()` method without replacement.

### Sonata\DoctrineMongoDBAdminBundle\Guesser\TypeGuesser

Deprecated `guessType()` method, you should use `guess()` method instead.

### Sonata\DoctrineMongoDBAdminBundle\Guesser\FilterTypeGuesser

Deprecated `guessType()` method, you should use `guess()` method instead.

### Sonata\DoctrineMongoDBAdminBundle\Guesser\AbstractTypeGuesser

This class has been deprecated without replacement.

### Sonata\DoctrineMongoDBAdminBundle\Model\ModelManager

Deprecated `getParentMetadataForProperty()` method.
Deprecated `getNewFieldDescriptionInstance()` method, you SHOULD use `FieldDescriptionFactory::create()` instead.
Deprecated passing an instance of `Sonata\AdminBundle\Datagrid\ProxyQueryInterface`
which is not an instance of `Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQueryInterface` as
argument 2 to the `Sonata\DoctrineMongoDBAdminBundle\Model\ModelManager::addIdentifiersToQuery()` method.
Deprecated passing an instance of `Sonata\AdminBundle\Datagrid\ProxyQueryInterface`
which is not an instance of `Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQueryInterface` as
argument 2 to the `Sonata\DoctrineMongoDBAdminBundle\Model\ModelManager::batchDelete()` method.
Deprecated `getModelInstance()` method.

### Sonata\DoctrineMongoDBAdminBundle\Filter\Filter

Deprecated passing an instance of `Sonata\AdminBundle\Datagrid\ProxyQueryInterface`
which is not an instance of `Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQueryInterface` as
argument 1 to the `Sonata\DoctrineMongoDBAdminBundle\Filter\Filter::filter()` method.

UPGRADE FROM 3.6 to 3.7
=======================

### Sonata\DoctrineMongoDBAdminBundle\Datagrid\Pager

Deprecated `computeNbResult()` method.
Deprecated `getNbResults()` method, you SHOULD use `countResults()` instead.
Deprecated `setNbResults()` method.
Deprecated `getResults()` method.

UPGRADE FROM 3.5 to 3.6
=======================

### Sonata\DoctrineMongoDBAdminBundle\Guesser\TypeGuesser

`TypeGuesser::guessType()` returns `null` instead of `false` when there is no metadata found for the property
respecting `TypeGuesserInterface::guessType()`.

### Sonata\DoctrineMongoDBAdminBundle\Model\ModelManager

Deprecated passing `null` as argument 2 for `find()`.
Deprecated passing `null` or an object which is in state new or removed as argument 1 for `getNormalizedIdentifier()`.
Deprecated passing `null` as argument 1 for `getUrlSafeIdentifier()`.
Deprecated `getModelIdentifier()`.
Deprecated `getDefaultSortValues()`.
Deprecated `getDefaultPerPageOptions()`.
Deprecated `modelTransform()`.
Deprecated `getDataSourceIterator()`. You SHOULD use
`Sonata\DoctrineMongoDBAdminBundle\Exporter\DataSource::createIterator` instead.

UPGRADE FROM 3.3 to 3.4
=======================

### Sonata\DoctrineMongoDBAdminBundle\Model\ModelManager

- Deprecated `ModelManager::getModelCollectionInstance()`.
- Deprecated `ModelManager::collectionClear()`.
- Deprecated `ModelManager::collectionHasElement()`.
- Deprecated `ModelManager::collectionAddElement()`.
- Deprecated `ModelManager::collectionRemoveElement()`.
- Deprecated `ModelManager::getPaginationParameters()`.
- Deprecated `ModelManager::getSortParameters()`.

### Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery

Deprecated passing arguments to `execute()` method.

### Sonata\DoctrineMongoDBAdminBundle\DatagridBuilder

Changed constructor's first parameter typehint from `Symfony\Component\Form\FormFactory` to
`Symfony\Component\Form\FormFactoryInterface`.

### Sonata\DoctrineMongoDBAdminBundle\Model\ModelManager

Deprecated `camelize()` method.

Deprecated not passing an instance of `PropertyAccessInterface` as second argument in the constructor.

### Sonata\DoctrineMongoDBAdminBundle\Admin\FieldDescription

Deprecated `getTargetEntity()`, use `getTargetModel()` instead.

UPGRADE FROM 3.0 to 3.1
=======================

### Tests

All files under the ``Tests`` directory are now correctly handled as internal test classes.
You can't extend them anymore, because they are only loaded when running internal tests.
More information can be found in the [composer docs](https://getcomposer.org/doc/04-schema.md#autoload-dev).
