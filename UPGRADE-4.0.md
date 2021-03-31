UPGRADE FROM 3.x to 4.0
=======================

### Sonata\DoctrineMongoDBAdminBundle\FieldDescription\TypeGuesser

When guessing a `FieldDescriptionInterface` type with association mapping:
- If the association type is `one`, the type of the `TypeGuess` instance is `many_to_one` instead of `mongo_one`.
- If the association type is `many`, the type of the `TypeGuess` instance is `many_to_many` instead of `mongo_many`.

## Deprecations

All the deprecated code introduced on 3.x is removed on 4.0.

Please read [3.x](UPGRADE-3.x.md) upgrade guides for more information.

See also the [diff code](https://github.com/sonata-project/SonataDoctrineMongoDBAdminBundle/compare/3.x...4.0.0).
