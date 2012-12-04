Installation
============

First install the Sonata Admin Bundle which provides Core functionalities.

Download bundles
----------------

Use composer ::

    php composer.phar require sonata-project/doctrine-mongodb-admin-bundle

Version constraint: dev-master

Configuration
-------------

Next, be sure to enable the bundles in your autoload.php and AppKernel.php
files:

.. code-block:: php

    <?php
    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new Sonata\DoctrineMongoDBAdminBundle\SonataDoctrineMongoDBAdminBundle(),
            // ...
        );
    }
