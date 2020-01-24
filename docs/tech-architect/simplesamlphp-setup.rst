.. include:: ../../common/global.rst

Setting up SimpleSAMLphp with Acquia BLT
========================================

Use the following information to set up single sign-on (SSO) with SimpleSAMLphp
on a working Acquia BLT website.

Acquia BLT provides commands for automating the setup process for SimpleSAMLphp
and assists in deploying configuration files to Acquia Cloud. You must
already be familiar with the process of configuring SimpleSAMLphp as described
in the :doc:`instructions for using SimpleSAMLphp on Acquia Cloud
</resource/simplesaml/>`.

Acquia BLT doesn't offer support for issues related to SimpleSAMLphp
architecture, configuration, or implementation. Direct SimpleSAMLphp
support requests to Acquia Support or your Technical Account Manager.

Before proceeding, prepare your SimpleSAMLphp configuration by completing the
following tasks:


#.  Run the following command to perform initial installation tasks:

    .. code-block:: text

       blt recipes:simplesamlphp:init

    Tasks completed by the initialization command include the following:

    -  Adds the `simpleSAMLphp
       Authentication <https://www.drupal.org/project/simplesamlphp_auth>`__
       module as a project dependency in your ``composer.json`` file.
    -  Copies configuration files to
       ``${project.root}/simplesamlphp/config``.
    -  Adds a ``simplesamlphp`` property to the ``blt/blt.yml`` file, which
       instructs Acquia BLT to include your SimpleSAMLphp configuration during
       deployments to Acquia Cloud.
    -  Creates a symbolic link in the docroot to the web-accessible
       directory of the ``simplesamlphp`` library.

#.  Follow the :doc:`instructions for using SimpleSAMLphp on Acquia Cloud
    </resource/simplesaml/>` to update the configuration files located in the
    ``${project.root}/simplesamlphp/config`` directory.

#. Run the following command to copy the configuration files to the
   local SimpleSAML library:

   .. code-block:: bash

       blt source:build:simplesamlphp-config

   .. note::

       The ``source:build:simplesamlphp-config`` command is strictly for local
       use, and because the command overwrites vendor files, running the
       command will make not make any changes that are visible to Git.

SimpleSAMLphp should now be ready for testing in your local environment. When
you are ready to test in an Acquia Cloud environment, commit your configuration
files and deploy a build artifact as usual using ``blt artifact:deploy`` or one
of Acquia BLT's supported continuous integration services. Acquia BLT will add
and commit your configuration files when building a deploy artifact.

.. Next review date 20200422
