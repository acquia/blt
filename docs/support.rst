.. include:: ../common/global.rst

Acquia BLT support
==================

The following resources are available for support with BLT issues:

- :doc:`/blt/` documentation on `docs.acquia.com <https://docs.acquia.com>`__
- `Acquia BLT Knowledge Base
  <https://support.acquia.com/hc/en-us/search#stq=Acquia+BLT&stp=1>`__ on `support.acquia.com <https://support.acquia.com>`__
- `Acquia BLT Github issue queue <https://github.com/acquia/blt/issues>`__
  (read :doc:`/blt/contributing` before opening an issue or pull request on
  Github)
- :ref:`Acquia Support <contact-acquia-support>` through a support ticket or
  through your Technical Account Manager
- Community support through the BLT channel in `Drupal Slack
  <https://www.drupal.org/slack>`__

The resources outlined on this page offer guidance on troubleshooting various
issues.


.. _blt-support-blt-dependencies:

Support for Acquia BLT dependencies
-----------------------------------

Acquia BLT provides automation for several other applications, including (but
not limited to) the following:

-  `Behat <https://behat.org/en/latest/>`__
-  `Composer <https://getcomposer.org/>`__
-  `Drupal 8 <https://www.drupal.org/8>`__
-  `Drupal VM <https://www.drupalvm.com/>`__
-  `Drush <https://www.drush.org/>`__
-  `Git <https://git-scm.com/>`__
-  `Gulp <https://gulpjs.com/>`__
-  `NPM <https://www.npmjs.com/>`__ / `Yarn <https://yarnpkg.com/lang/en/>`__
-  `PHPCS <https://github.com/squizlabs/PHP_CodeSniffer>`__
-  `PHPUnit <https://phpunit.de/>`__

As a result, various *issues with Acquia BLT* are actually *issues with one of
the bundled applications*. Acquia strongly recommends a careful review of the
error messages returned by your project. The error messages often direct you
to the underlying application which is the true source of the issue instead of
Acquia BLT.


.. _blt-basic-troubleshooting:

Basic troubleshooting
---------------------

If you experience issues with a local Acquia BLT build, try using the
included ``blt doctor`` command to diagnose your problem:

.. code-block:: bash

   blt doctor

If you are having problems with a specific command, run the preceding command
again with the ``-vvv`` argument (for example, ``blt setup -vvv``). By
including the verbose flag, the command provides verbose output and enumerates
any underlying commands (such as Drush or Composer) called by Acquia BLT.

If the verbose output identifies a specific command that's failing, try running
the command directly without invoking Acquia BLT. This will determine whether
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
the issue will be easier to debug without involving Acquia BLT. If you still
can't diagnose the issue, :ref:`contact Acquia Support
<contact-acquia-support>`. Acquia recommends not opening an issue in the BLT
queue unless you've identified a specific bug or feature request for Acquia
BLT.


.. Next review date 20200424
