Doctrine ORM Proxy Query
========================


The ``ProxyQuery`` object is used to add missing features from the original Doctrine Query builder :

  - ``execute`` method - no need to call the ``getQuery()`` method
  - add sort by and sort order options


.. code-block:: php

    <?php
    use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery;

    $queryBuilder = $this->em->createQueryBuilder();

    $proxyQuery = new ProxyQuery($queryBuilder);
    $proxyQuery->setSortBy('name');
    $proxyQuery->setMaxResults(10);

    $results = $proxyQuery->execute();