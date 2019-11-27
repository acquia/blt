.. include:: ../common/global.rst

Acquia BLT support
==================

The following resources are available for support with BLT issues:

- [Acquia BLT Documentation](https://docs.acquia.com/blt/) on [docs.acquia.com](https://docs.acquia.com).
- [Acquia BLT Knowledge Base](https://support.acquia.com/hc/en-us/search#stq=Acquia+BLT&stp=1) on [support.acquia.com](https://support.acquia.com/hc/en-us).
- [Acquia BLT Github issue queue](https://github.com/acquia/blt/issues) (read [CONTRIBUTING.md](https://github.com/acquia/blt/blob/10.x/docs/CONTRIBUTING.md) before opening an issue or pull request on Github)
- Acquia Support via a Support Ticket or your Technical Account Manager
- Community support via the BLT channel in Drupal Slack

Read on for guidance in troubleshooting issues with the help of these resources.

Support for Acquia BLT dependencies
-----------------------------------

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
error messages returned by your project, which frequently direct you more
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
command directly without invoking Acquia BLT. This will indicate whether
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

To debug the problem, run the Drush command directly from the command line, as
it will be easier to debug without involving Acquia BLT. If you still cannot diagnose the issue, contact Acquia Support. Do not open an issue in the BLT queue unless you have identified a specific bug or feature request for Acquia BLT itself.


.. Next review date 20200424
