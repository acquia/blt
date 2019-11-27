.. include:: ../../../common/global.rst

Configuring Acquia BLT with ddev
================================

Complete the following steps to configure your Acquia BLT project to use
ddev:

#. `Install ddev <https://ddev.readthedocs.io/en/stable/#installation>`__.
#. Run the following command, replacing ``[example_site]`` with your
   website's name:

   .. code-block:: bash

      export SITENAME=[example_site]

#. Run the following command, replacing ``[example_site]`` with your project
   name:

   .. code-block:: bash

      composer create-project –no-interaction acquia/blt-project [example_site]

#. Run the following command:

   .. code-block:: bash

      ddev config –docroot docroot –projectname $SITENAME –projecttype drupal8

#. Run the following command:

   .. code-block:: bash

      ddev start

#. Run the following command:

   .. code-block:: bash

      ddev ssh

#. Run the following command:

   .. code-block:: bash

      blt

#. Edit your ``[example_site].ddev/config.yaml`` file (where ``[example_site]``
   is your project name) to add the following lines:

   .. code-block:: yaml

        hooks:
        post-start:
        - exec: "ln -sf /var/www/html/vendor/acquia/blt/bin/blt /usr/bin/blt"
        - exec: bash -c "sudo apt-get update && sudo apt-get install -y php7.1-bz2"

.. Next review date 20200424
