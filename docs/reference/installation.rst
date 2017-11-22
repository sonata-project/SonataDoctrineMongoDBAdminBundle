Installation
============

SonataDoctrineMongoDBAdminBundle is part of a set of bundles aimed at abstracting 
storage connectivity for SonataAdminBundle. As such, SonataDoctrineMongoDBAdminBundle
depends on SonataAdminBundle, and will not work without it. 

.. note::
    These installation instructions are meant to be used only as part of SonataAdminBundle's
    installation process, which is documented `here <http://sonata-project.org/bundles/admin/master/doc/reference/installation.html>`_.

Download bundles
----------------

Use composer:

.. code-block:: bash

    php composer.phar require sonata-project/doctrine-mongodb-admin-bundle

You'll be asked to type in a version constraint. 'dev-master' will usually get you the latest, bleeding edge
version. Check `packagist <https://packagist.org/packages/sonata-project/doctrine-mongodb-admin-bundle>`_
for stable and legacy versions:

.. code-block:: bash

    Please provide a version constraint for the sonata-project/doctrine-mongodb-admin-bundle requirement: dev-master

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
            // set up basic sonata requirements
            // ...
            new Sonata\DoctrineMongoDBAdminBundle\SonataDoctrineMongoDBAdminBundle(),
            // ...
        );
    }

.. note::
    Don't forget that, as part of `SonataAdminBundle's installation instructions <http://sonata-project.org/bundles/admin/master/doc/reference/installation.html>`_,
    you need to enable additional bundles on AppKernel.php