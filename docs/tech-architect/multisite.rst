.. include:: ../../common/global.rst

Multisite
=========

Configuring a multisite instance on Acquia BLT involves two parts:
the local configuration and the Acquia Cloud configuration.


.. _blt-multisite-local-config:

Local configuration
-------------------

#. Set up a single website on BLT, following the standard instructions, and
   ssh to the vm (``vagrant ssh``).

#. Run ``blt recipes:multisite:init``. It is suggested to use an easy
   machine name (rather than a domain name) for your site for `maximum
   compatibility with other BLT features
   <https://github.com/acquia/blt/pull/3503#issuecomment-477416463>`__.

   Running ``blt recipes:multisite:init``\ performs the following:

   -  Sets up new a directory in your ``docroot/sites`` directory with the
      multisite name given with all the necessary files and
      subdirectories.
   -  Sets up a new drush alias.
   -  Sets up a new ``vhost`` in the ``box/config.yml`` file.
   -  Sets up a new MySQL user in the ``box/config.yml`` file.

   Some manual configuration is still required using the following steps.

#. Set the new website's local database credentials in the
   ``docroot/sites/{newsite}/settings/local.settings.php`` file to
   ensure your new website connects to the correct database.

#. Copy core's ``example.sites.php`` file, rename it to ``sites.php``,
   and add entries for your new website.

#. If desired override any BLT settings in the
   ``docroot/sites/{newsite}/blt.yml`` file.

#. Once you've completed the above and any relevant manual steps, exit
   out of your virtual machine environment and update with the new
   configuration using ``vagrant provision``.


.. _blt-multisite-optional-local-configuration:

Optional local configuration steps
----------------------------------

Add a multisite array
~~~~~~~~~~~~~~~~~~~~~

You have the option to explicitly define your multisites in
``blt/blt.yml`` by creating a ``multisites`` array. If you don't
manually define this variable, Acquia BLT will set it based on
discovered multisite directories:

.. code-block:: text

   multisites:
     - default
     - example

At this point you should have a functional multisite codebase that can
be installed on Acquia Cloud.


Override BLT variables
~~~~~~~~~~~~~~~~~~~~~~

You may override Acquia BLT variables on a per-website basis by
editing the ``blt.yml`` file in ``docroot/sites/{newsite}/``. You may then run
Acquia BLT with the ``site`` variable set at the command line to
load the website's properties.

For instance, if the ``drush`` aliases for your website in
``docroot/sites/mysite`` where ``@mysite.local`` and ``@mysite.test``,
you could define these in ``docroot/sites/mysite/blt.yml`` as:

.. code-block:: yaml

   drush:
     aliases:
       local: mysite.local
       remote: mysite.test

Then, to refresh your local website, you could run:
``blt drupal:sync --site=mysite``.


.. _blt-multisite-ac-config:

Acquia Cloud configuration
--------------------------

Start by following the :doc:`/acquia-cloud/develop/drupal/multisite/` to
configure your codebase for Acquia Cloud. Specifically, these
instructions should walk you through:

#. Creating a new database in Acquia Cloud.

#. Adding the website-specific settings include to each website's
   ``settings.php`` file. In the ``settings.php`` for your multisite, add the
   ``require`` statement for your multisite database credentials *before* the
   ``require`` statement for ``blt.settings.php``, for example:

   .. code-block:: text

      if (file_exists('/var/www/site-php')) {
         require '/var/www/site-php/mysite/multisitename-settings.inc';
      }

      require DRUPAL_ROOT . "/../vendor/acquia/blt/settings/blt.settings.php";

Drush aliases
~~~~~~~~~~~~~

The default Drush website aliases provided by :doc:`Acquia
Cloud </acquia-cloud/manage/ssh/drush/aliases/>` and `Club
<https://github.com/acquia/club#usage>`__ are not currently
multisite-aware. They will connect to the first ("default") website /
database on the subscription by default. You will need to create your
own Drush aliases for each site.

Acquia recommends copying the aliases file provided by Acquia Cloud
or Club to create a separate aliases file for each website. Change the
``uri`` and ``parent`` keys for the aliases within each file to match
the correct database or website.

.. Next review date 20200423
