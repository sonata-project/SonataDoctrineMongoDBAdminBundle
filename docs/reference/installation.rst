Installation
============

SonataDoctrineMongoDBAdminBundle is part of a set of bundles aimed at abstracting 
storage connectivity for SonataAdminBundle. As such, SonataDoctrineMongoDBAdminBundle
depends on SonataAdminBundle, and will not work without it. 

.. note::

    These installation instructions are meant to be used only as part of SonataAdminBundle's
    installation process, which is documented `here <http://sonata-project.org/bundles/admin/master/doc/reference/installation.html>`_.

Download the Bundle
-------------------

.. code-block:: bash

    composer require sonata-project/doctrine-mongodb-admin-bundle

Enable the Bundle
-----------------

Then, enable the bundle by adding it to the list of registered bundles
in ``bundles.php`` file of your project::

    // config/bundles.php

    return [
        // ...
        Sonata\DoctrineMongoDBAdminBundle\SonataDoctrineMongoDBAdminBundle::class => ['all' => true],
    ];
