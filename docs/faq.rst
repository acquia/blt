.. include:: ../common/global.rst

Acquia BLT FAQ and support
==========================

Acquia BLT provides automation for several other applications, including (but
not limited to) the following:

-  Behat
-  Composer
-  Drupal 8
-  Drupal VM
-  Drush
-  Git
-  Gulp
-  NPM / Yarn
-  PHPCS
-  PHPUnit

As a result, many *issues with Acquia BLT* are actually *issues with one of
the bundled applications*. Acquia strongly recommends a careful review of the
errors messages returned by your project, which frequently direct you more
appropriately to the underlying application that's the true cause (and not
Acquia BLT itself).


.. _blt-basic-troubleshooting:

Basic troubleshooting
---------------------

If you experience issues with a local Acquia BLT build, try using the
included ``blt doctor`` command to diagnose your problem:

.. code-block:: bash

      blt doctor

If you are having problems with a specific command, run that command again
with the ``-vvv`` argument (for example, ``blt setup -vvv``). This will
provide verbose output and enumerate any underlying commands (such as Drush
or Composer) called by Acquia BLT.

If this identifies a specific command that's failing, try running that
command directly, without invoking Acquia BLT). This will indicate whether
you have a problem with Acquia BLT or with another application, such as Drush
or Composer.

For instance, running ``blt setup -vvv`` may return the following results:

.. code-block:: text

         [drush] Changing working directory to: /Users/me/Sites/mysite/docroot
         [drush] Executing: /Users/me/Sites/mysite/vendor/bin/drush
         @blted10.local --site-name="BLTed 10"
         --site-mail="no-reply@acquia.com" --account-name="admin"
         --account-pass="admin" --account-mail="no-reply@acquia.com"
         --uri=default --yes --verbose site-install "lightning"
         "install_configure_form.update_status_module='array(FALSE,FALSE)'"
         Loaded alias @blted10.local from file

In this case, Acquia BLT is executing the following Drush command for you:

.. code-block:: text

        /Users/me/Sites/mysite/vendor/bin/drush @blted10.local
        --site-name="BLTed 10" --site-mail="no-reply@acquia.com"
        --account-name="admin" --account-pass="admin"
        --account-mail="no-reply@acquia.com" --uri=default --yes --verbose
        site-install "lightning"
        "install_configure_form.update_status_module='array(FALSE,FALSE)'"

To debug the problem, run the Drush command directly from the command line.
It may be easier to navigate without Acquia BLT. After you resolve the issue,
go back to using Acquia BLT's automation layer.


.. _blt-faq-common-issues-solutions:

Common Acquia BLT issues and solutions
--------------------------------------

Acquia BLT command failure (generic)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**Error Message**

.. code-block:: text

      You must run this command from within a BLT-generated project repository.

**Solution**

If you have trouble using the ``blt`` alias, ensure it's installed correctly,
and then restart your terminal session:

.. code-block:: bash

      composer run-script blt-alias
      source ~/.bash_profile

PHP syntax errors / doctrine errors
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**Error Message**

.. code-block:: php

      PHP Parse error:  syntax error, unexpected ':', expecting ';' or '{' in /var/www/<project>/vendor/doctrine/annotations/lib/Doctrine/Common/Annotations/AnnotationRegistry.php on line 50

      Parse error: syntax error, unexpected ':', expecting ';' or '{' in /var/www/<project>/vendor/doctrine/annotations/lib/Doctrine/Common/Annotations/AnnotationRegistry.php on line 50
      Drush command terminated abnormally due to an unrecoverable error.                                                                             [error]
      Error: syntax error, unexpected ':', expecting ';' or '{' in
      /var/www/<project>/vendor/doctrine/annotations/lib/Doctrine/Common/Annotations/AnnotationRegistry.php, line 50
      [Acquia\Blt\Robo\Tasks\DrushTask]  Exit code 255  Time 10.708s

**Solution**

This error frequently occurs when the PHP version committed in your Composer
files differs from the PHP version on the computer.

This error can affect virtual machines, continuous integration environments,
and Cloud environments.

Ensure that all environments are running the same version of PHP. This can be
accomplished by changing PHP versions on the computers, or by *locking* the
PHP version in your Composer file, and then re-running ``composer update``.

Refer to the following example (based on your installed version of
Acquia BLT) for how to lock PHP version to the appropriate version in the
``composer.json`` file:

.. tabs::

   .. tab:: 10.x

      .. code-block:: text

              "require": {
                "php": "7.2"
              },

   .. tab:: 9.2.x

      .. code-block:: text

              "config": {
                "platform": {
                  "php": "5.6"
                }
              },

Robo default config error
~~~~~~~~~~~~~~~~~~~~~~~~~

**Error Message**

.. code-block:: text

   PHP Notice:  Undefined property:
   Acquia\Blt\Robo\Config\DefaultConfig::$config in /var/www/vendor/acquia/blt/src/Robo/Config/DefaultConfig.php on line 70

**Solution**

Update to a more recent version of Acquia BLT or manually pin Robo in your
``composer.json`` file:

.. code-block:: text

      "consolidation/robo": "~1.2.4"

Continuous integration errors
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**Issue**

Errors appearing in TravisCI can't be replicated on your local or other
environments.

**Solution**

`TravisCI has an internal caching feature
<https://docs.travis-ci.com/user/caching>`__ which can help speed up builds.
Periodically, however, this cache results in build failures that can't be
replicated elsewhere. In these instances, you can `clear Travis's cache
<https://docs.travis-ci.com/user/caching/#Clearing-Caches>`__ to try to
resolve the issue.


Permission denied during SQL sync or Acquia BLT sync
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**Issue**

During commands that include ``drush sql-sync``, ``blt sync``, or
``blt sync:refresh``, the commands fail and display error messages like
the following:

.. code-block:: text

     sh: 1: cannot create auto.gz: Permission denied
      [error]  Database dump failed [3.1 sec, 8 MB]

This issue was originally documented in `Acquia BLT issue #2641
<https://github.com/acquia/blt/issues/2641>`__.

**Solution**

This is most likely an issue of Drush version mismatches between
environments. If you are running Drush 9 locally but Drush 8 in your remote
environment, you will encounter this issue.

This issue was
`documented <https://github.com/drush-ops/drush/releases/tag/9.2.1>`__ by
Drush's developers.

Use either of the following options to try to resolve this issue:

*  Deploy Drush 9 to the remote environment.
*  Temporarily add a ``--source-dump`` option (based on the Drush
   documentation) during the ``sql-sync`` command.


Dirty source directory prevents deploys
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**Issue**

When trying to deploy code, the following error message is displayed:

.. code-block:: text

    There are uncommitted changes, commit or stash these changes before deploying

**Background**

Before deploying code, Acquia BLT ensures that the source directory is
*clean* according to Git, which ensures that any changes being deployed are
captured in your source repository. This is especially important in a CI
environment, to ensure that nothing during the testing process modified the
codebase in manner that could cause undefined or undesirable behavior after
it's deployed. For instance, this prevents the testing process from changing
database credentials that then get deployed to a production environment.

**Solution**

Ensure that your Git directory is clean before deploying. Acquia BLT should
return a list of all dirty files to help you debug. If deploying locally,
this involves committing the changes. If deploying using continuous
integration (CI), you'll need to determine what might be causing these files
to change during the test process.

Examples of what can cause files to change during the deploy process and how
to troubleshoot them are as follows:

*  If you have defined frontend build steps that call ``npm install``,
   you can modify ``package-lock.json`` during deployments. Try using ``npm
   ci`` instead, see :doc:`/blt/developer/frontend/` for details. Warning:
   ``npm ci`` isn't present in older versions of npm.
*  Ensure you commit the files' permissions properly (as git will track a
   file as ``M`` if the diff is the same, but permissions differ),
*  Try replicating the CI process locally by running the same commands
   (visible in the CI logs), such as ``blt setup`` and ``blt tests:all``. If
   these commands change files locally, you should determine if these changes
   need to be committed, or whether your test scripts need to be adjusted to
   avoid creating changes.
*  Run the ``blt doctor`` command locally to ensure there are no problems,
   such as missing settings file includes.

For additional documentation and solutions, refer to this `Acquia BLT issue
<https://github.com/acquia/blt/issues/3564>`__.

.. important::

   In an emergency, you can disable this check by passing the
   ``--ignore-dirty`` flag to the ``blt deploy`` command, but this is
   strongly discouraged, as it may conceal deeper issues with your codebase.


.. _blt-faq:

Frequently asked questions
--------------------------

**Can I change the install profile? Do I have to use Lightning with Acquia
BLT?**

You can use any install profile you want—from core, contrib, or your own
custom development. Acquia BLT defaults to :doc:`/lightning/` if you don't
have a preference.

For more information, see :doc:`/blt/install/creating-new-project/`.

.. Next review date 20200424
