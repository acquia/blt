.. include:: ../../common/global.rst

PHPStorm
========

Acquia BLT includes several components that can be used with PHPStorm,
including the following:

-  An editor configuration file.
-  A set of PHPCS rules and associated configuration.
-  DrupalVM with Xdebug configuration.

Here is the recommended setup process for new projects in PHPStorm.

.. note::

   These instructions were written for PHPStorm 2018.1, and may need to be
   adjusted for other versions.


.. _blt-setting-up-inspections:

Setting up inspections
----------------------

PHPStorm includes many built-in inspections for PHP. You can enable or disable
each inspection individually in the user interface. Most of the inspections
make sense in the context of Drupal or Acquia BLT projects, but not all.

To ensure consistent validation across all environments on your project,
it's better to disable PHPStorm's built-in inspections and instead
delegate code sniffing to Coder and PHPCodeSniffer. Use the
``phpcs.xml`` rule sets that you distribute with your project (and that BLT
provides by default).

To do this, complete the following steps:

#.  Open PHPStorm, and in its main menu, go to **PHPStorm > Preferences**.
#.  Go to **Languages & Frameworks > PHP > Code Sniffer**.
#.  Open the window next to **Configuration**, and then select the PHPCS
    binary in your project that is located at ``vendor/bin/phpcs``.
#.  Go to **Editor > Inspections**.
#.  Clear the **PHP** check box to disable all PHP inspections.
#.  Select the **Undefined > Undefined variable analysis** check box
    (as Coder 2 doesn't support this).
#.  Select the **Quality tools > PHP Code Sniffer validation** check box.
#.  In **PHP CodeSniffer configuration**, change the coding standard to
    **custom**, and then select the ``phpcs.xml`` file in the root of your
    project.


.. _blt-setting-up-xdebug:

Setting up Xdebug
-----------------

For a tutorial about how to set up PHPStorm, Xdebug, and DrupalVM to work
together, see https://danepowell.com/blog/xdebug-phpstorm-drupalvm.

Additionally, to support the following PHPUnit testing and debugging
instructions, you can enable Xdebug for the CLI SAPI:

.. code-block:: text

   php_xdebug_cli_disable: no


.. _blt-setting-up-phpunit-testing-debugging:

Setting up PHPUnit testing and debugging
----------------------------------------

To use PHPStorm, Xdebug, and DrupalVM together in a Drupal testing
workflow, you will need to complete some additional configuration:

#.  Configure the remote CLI interpreter.

    a.  Go to **PHPStorm settings / preferences**.
    #.  Go to **Languages & Frameworks > PHP**.
    #.  Open the window next to **CLI Interpreter**.
    #.  Click the plus ( **+** ) to add a new interpreter, and then select
        **From Docker, Vagrant, VM, Remote…**.
    #.  In the **Configure Remote PHP Interpreter** dialog box, click
        **Vagrant**, and then set the **Vagrant Instance Folder** to the
        root of your project.

#.  Configure the debug settings.

    a.  Go to **PHPStorm settings / preferences**.
    #.  Go to **Languages & Frameworks > PHP > Debug**.
    #.  Select the **Force break at first line when no path mapping
        is specified** check box under **Xdebug**.
    #.  Select the **Force break at first line when a script is outside the
        project** check box under **Xdebug**.

#.  Configure your server settings.

    a.  Go to **PHPStorm settings / preferences**.
    #.  Go to **Languages & Frameworks > PHP > Servers**.
    #.  Click the plus ( **+** ) to add a new sever configuration.
    #.  Enter the host name of your project in both the **Name** and **Host**
        fields (for example ``local.my-project.com``).

#.  Configure the test frameworks.

    a.  Go to **PHPStorm settings / preferences**.
    #.  Open **Languages & Frameworks > PHP > Test Frameworks**.
    #.  Click the plus ( **+** ) to add a new test framework configuration,
        and then click **PHPUnit by Remote Interpreter**.
    #.  Select the remote CLI interpreter you configured in a previous step of
        this process.
    #.  In **PHP Unit library**, click **Use Composer autoloader**.
    #.  Open the window next to **Path to script**, and then select the
        autoloader file in your project at ``vendor/bin/phpcs``.
    #.  In **Test Runner**, click **Default configuration file**, and then
        select the configuration file in your project, as
        ``docroot/core/phpunit.xml``.

#.  Configure your test configurations.

    a.  From the main menu, go to **Run > Edit Configurations**.
    #.  Click the plus ( **+** ) to add a new configuration, and then click
        **PHPUnit**.
    #.  In the **Name** field, enter the hostname of your project (for
        example, ``local.my-project.com``).
    #.  In **Test Runner**, click **Defined in the configuration file**.
    #.  Optionally, add any additional configuration or commands you want to
        use for testing.

#.  Optionally, install and use
    https://github.com/mglaman/intellij-drupal-run-tests for an additional
    set of test configurations specifically for Drupal.

.. Next review date 20200422
