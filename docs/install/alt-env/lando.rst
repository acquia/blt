.. include:: ../../../common/global.rst

Configuring Acquia BLT with Lando
=================================

Acquia BLT with `Lando <https://docs.devwithlando.io/>`__ generally works as
expected, but to achieve a better integration, add the following code to
your ``.lando.yml`` file, and then rebuild the containers:

.. code-block:: text

   tooling:
     blt:
       service: appserver
   cmd: /app/vendor/bin/blt

You can also update your ``blt config`` (``blt.yml``) with the Lando host name
syntax:

.. code-block:: text

   project:
     machine_name: abc
     prefix: ABC
     human_name: 'A website'
     profile:
       name: standard
     local:
       protocol: http
       hostname: ${project.machine_name}.lndo.site


.. _blt-lando-known-issues:

Lando known issues
------------------

The Acquia BLT integration with Lando has the following known issues:


.. _blt-lando-known-driver:

Missing Chrome driver
~~~~~~~~~~~~~~~~~~~~~

Depending on your Lando recipe, running ``lando blt tests:behat`` may display
an error message about a missing Chrome driver. If you encounter this error
message, add the driver installation to your Lando recipe.


.. _blt-lando-known-chrome:

Chrome timeouts
~~~~~~~~~~~~~~~

If you receive timeout error messages, try running the ``blt`` command with
the ``-vvv`` option for more output (``lando blt -vvv tests:behat``). If you
receive the following error messages, the issue is with the permissions on
your container:

.. code-block:: text

   [Filesystem\FilesystemStack] mkdir ["/app/reports"]
   [info] Killing running google-chrome processes...
   [info] Killing all processes on port '9222'...
   [info] Launching headless chrome...
   [Robo\Common\ProcessExecutor] Running 'google-chrome' --headless --disable-web-security --remote-debugging-port=9222  http://localhost in /app
   [info] Waiting for response from http://localhost:9222...
   [debug] cURL error 7: Failed to connect to localhost port 9222: Connection refused (see http://curl.haxx.se/libcurl/c/libcurl-errors.html)
   ...

   [info] Killing running google-chrome processes...
   [info] Killing all processes on port '9222'...
   [error]  Timed out.
   12.412s total time elapsed.

If you ``ssh`` in to Lando and then run the ``google-chrome`` command, the
following output will be displayed:

.. code-block:: text

   $ google-chrome --headless --disable-web-security --remote-debugging-port=9222  http://localhost
   Failed to move to new namespace: PID namespaces supported, Network
   namespace supported, but failed: errno = Operation not permitted
   Failed to generate minidump.Illegal instruction

The solution is to invoke the ``chrome`` command with the ``--no-sandbox``
option by patching your Acquia BLT installation. Patching your installation
adds the ``--no-sandbox`` option to the `launchChrome() function in the Behat
command
<https://github.com/acquia/blt/blob/10.x/src/Robo/Commands/Tests/BehatCommand.php#L178>`__.

For information about how to apply patches to packages using Composer, see
:doc:`/blt/developer/patches/`.


.. _blt-lando-known-keys:

Undefined index notices for $_SERVER keys
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When running several Acquia BLT or Drush commands through Lando when
initializing Acquia Cloud Site Factory, the following notices may display:

.. code-block:: text

   <em class="placeholder">Notice</em>: Undefined index: HTTP_HOST in <em class="placeholder">require()</em> (line <em class="placeholder">119</em> of <em class="placeholder">/app/vendor/acquia/blt/settings/blt.settings.php</em>). <pre class="backtrace">require(&#039;/app/vendor/acquia/blt/settings/blt.settings.php&#039;) (Line: 797)
   require(&#039;/app/docroot/sites/default/settings.php&#039;) (Line: 122)
   Drupal\Core\Site\Settings::initialize(&#039;/app/docroot&#039;, &#039;sites/default&#039;, Object) (Line: 1056)
   Drupal\Core\DrupalKernel-&gt;initializeSettings(Object) (Line: 271)
   Drupal\Core\DrupalKernel::createFromRequest(Object, Object, &#039;prod&#039;, 1) (Line: 172)
   Drush\Boot\DrupalBoot8-&gt;bootstrapDrupalConfiguration(NULL) (Line: 295)
   Drush\Boot\BootstrapManager-&gt;doBootstrap(3, 6, NULL) (Line: 504)
   Drush\Boot\BootstrapManager-&gt;bootstrapMax() (Line: 224)
   Drush\Application-&gt;bootstrapAndFind(&#039;csex&#039;) (Line: 191)
   Drush\Application-&gt;find(&#039;csex&#039;) (Line: 229)
   Symfony\Component\Console\Application-&gt;doRun(Object, Object) (Line: 148)
   Symfony\Component\Console\Application-&gt;run(Object, Object) (Line: 112)
   Drush\Runtime\Runtime-&gt;doRun(Array) (Line: 41)
   Drush\Runtime\Runtime-&gt;run(Array) (Line: 66)
   require(&#039;/app/vendor/drush/drush/drush.php&#039;) (Line: 17)
   drush_main() (Line: 141)
   require(&#039;phar:///usr/local/bin/drush/bin/drush.php&#039;) (Line: 10)
   </pre>

   Notice: Undefined index: argv in Symfony\Component\Console\Input\ArgvInput->__construct() (line 53 of /app/vendor/symfony/console/Input/ArgvInput.php).

   Symfony\Component\Console\Input\ArgvInput->__construct(NULL) (Line: 113)
   require('/app/vendor/acquia/blt/settings/blt.settings.php') (Line: 797)
   require('/app/docroot/sites/default/settings.php') (Line: 122)
   Drupal\Core\Site\Settings::initialize('/app/docroot', 'sites/default', Object) (Line: 1056)
   Drupal\Core\DrupalKernel->initializeSettings(Object) (Line: 656)
   Drupal\Core\DrupalKernel->handle(Object) (Line: 19)

Lando does not populate the ``$_SERVER['HTTP_HOST]`` and ``$_SERVER['argv']``
variables. Beyond warnings and notices displayed on the screen, the impact is
unknown.

.. Next review date 20200417
