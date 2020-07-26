UPGRADE 3.x
===========

UPGRADE FROM 3.x to 3.x
=======================

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
