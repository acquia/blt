.. include:: ../common/global.rst

:orphan:

Extending and overriding Acquia BLT
===================================

Acquia BLT provides an extensive plugin and configuration system to support
customization.

.. _blt-add-robo-hook:

Adding a custom Robo hook or command
------------------------------------

Acquia BLT uses `Robo <https://github.com/consolidation/Robo>`__ and the
`Annotated Command <https://github.com/consolidation/annotated-command>`__
library to define commands. You can use annotated commands to define new custom
commands or hook into existing commands, such as executing custom code before
or after an existing Acquia BLT command.

You can generate a file containing an example custom command and hook by
running the following command:

.. code-block:: bash

   blt recipes:blt:command:init

You can place custom commands in a different directory in your project or even
a separate Composer package, as long as you expose the command file using PSR4.

To create your own Robo PHP command or hook, complete the following steps:

#.  Create a new file named using the required pattern ``*Commands.php``. For
    instance, the generated example uses the file name ``ExampleCommands.php``.

#.  Use a namespace ending in ``*\Blt\Plugin\Commands`` exposed using PSR4 in
    your ``composer.json`` file. For instance, the generated example uses the
    namespace ``Example\Blt\Plugin\Commands``.

#.  Follow the `Robo PHP Getting Started guide
    <http://robo.li/getting-started/#commands>`__ to write a custom command.
    For a list of all available hook types, see `Annotated Command's hook types
    <https://github.com/consolidation/annotated-command#hooks>`__.


.. _blt-replacing-overriding-robo-command:

Replacing or overriding a Robo command
--------------------------------------

To replace an Acquia BLT command with your own custom version, use the
`replace command annotation
<https://github.com/consolidation/annotated-command#replace-command-hook>`__
for your custom command.

.. note::

   If you replace an Acquia BLT command, you take responsibility for
   maintaining your custom command. Your command may break when changes are
   made to the upstream version of the command in Acquia BLT itself.


.. _blt-disabling-a-command:

Disabling a command
-------------------

You can disable any Acquia BLT command. This will cause the target to be
skipped during the normal build process. To disable a target, add a
``disable-targets`` key to your ``blt.yml`` file, as follows:

.. code-block:: text

      disable-targets:
        tests:
          phpcs:
            sniff:
              all: true
              files: true

This snippet causes Acquia BLT builds to skip the ``tests:phpcs:sniff:all``
and ``tests:phpcs:sniff:files`` targets.


.. _blt-add-override-fileset:

Adding or overriding filesets
-----------------------------

You can also define your own custom filesets or override existing filesets.

.. note::

   To change the behavior of PHPCodeSniffer, see the documentation on the
   ``tests:phpcs:sniff:all`` command in :ref:`the following section <blt-override-runtime>`.


You can generate a file with an example custom fileset by running the following
command:

.. code-block:: bash

   blt recipes:blt:filesystem:init

You can place custom commands in a different directory in your project or even
a separate Composer package, as long as you expose the command file using PSR4.

To create your own fileset definition, complete the following steps:

#.  Create a new file named using the required pattern ``*Filesets.php``. For
    instance, the generated example uses the file name ``ExampleFilesets.php``.

#.  Use a namespace ending in ``*\Blt\Plugin\Filesets`` exposed using PSR4 in
    your ``composer.json`` file. For instance, the generated example uses the
    namespace ``Example\Blt\Plugin\Filesets``.

#.  Create a public method in the ``Filesets`` class in the generated file.
#.  Add a Fileset annotation to your public method, specifying its id:

    .. code-block:: text

          @fileset(id="files.yaml.custom")

#.  Instantiate and return a ``Symfony\Component\Finder\Finder`` object. The
    files found by the finder form the fileset.

To change the filesets used in commands such as ``tests:twig:lint:all``,
``tests:yaml:lint:all``, and ``tests:php:lint``, add the following
configuration key to ``blt/blt.yml``:

.. code-block:: yaml

      validate:
        yaml:
          filesets:
            - files.yaml.custom


.. _blt-overriding-env-detector:

Overriding the environment detector
-----------------------------------

Acquia BLT includes a unified `environment detector class
<https://github.com/acquia/blt/blob/HEAD/src/Robo/Common/EnvironmentDetector.php>`__
providing information about the current hosting environment such as the
stage (``dev``, ``stage``, or ``prod``), provider (Acquia or Pantheon), and
type (local or CI). The environment detector primarily examines environment
variables and system configuration files to give details about the current
environment.

You can extend the environment detector to support custom environments or
override the detection behavior of built-in environments.

.. note::

   Page requests (due to ``settings.php`` includes) and BLT commands, can both
   invoke the environment detector, so performance is critical. The detector
   can't depend on a UI, the Drupal container, or Robo configuration.

To override environment detector methods, create a new BLT plugin as follows:

#.  Create a new custom environment detector class implementing Acquia BLT's
    environment detector.
#.  Override any supported method in your custom class.
#.  Expose your custom class using PSR4 and add your class to Composer's
    ``classmap`` in your plugin's ``composer.json`` file.

Acquia BLT's Environment Detector will discover your overrides using PSR4 and
re-dispatch any method calls to your custom implementation.

As a reference implementation, the `BLT Tugboat plugin
<https://github.com/acquia/blt-tugboat>`__ illustrates how to override the
Environment Detector in practice.

For more discussion on the Environment Detector architecture, design choices,
and performance considerations, see `this issue
<https://github.com/acquia/blt/issues/3804#issuecomment-523623896>`__.


.. _blt-modify-blt-config:

Modifying your Acquia BLT configuration
---------------------------------------

You can customize your Acquia BLT configuration by overriding the value of
default variable values. The `build.yml
<https://github.com/acquia/blt/blob/HEAD/config/build.yml>`__ file contains
the default values of all Acquia BLT variables.


.. _blt-override-variable:

Overriding a variable value
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Acquia BLT loads configuration values from the following list of YAML files,
in the listed order:

#.  ``vendor/acquia/blt/config/build.yml``
#.  ``blt/blt.yml``
#.  ``blt/[environment].blt.yml``
#.  ``docroot/sites/[site]/blt.yml``
#.  ``docroot/sites/[site]/[environment].blt.yml``

Values loaded from the later files will overwrite values in earlier files.

.. note::

   If you want to override a non-empty value with an empty value, the
   override value must be set to ``null``, and not ``''`` or ``[]``.


.. _blt-override-project:

Overriding variables project-wide
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can override any variable value by adding an entry for that variable to
your ``blt/blt.yml`` file. This change will be committed to your repository
and shared by all developers for the project. For example:

.. code-block:: text

      behat.tags: @mytags


.. _blt-override-local:

Overriding variables locally
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can override a variable value for your local computer in the same way that
you can for specific environments.

For instructions about how to do this, see the following section, and use
``local`` for the environment value.


.. _blt-override-var-env:

Overriding variables in specific environments
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can override a variable value for specific environments (such as the
``local`` or ``ci`` environments) by adding an entry for the variable to a
file named in the pattern of ``[environment].blt.yml`` (for example,
``ci.blt.yml``).

Acquia BLT detects only the ``local`` and ``ci`` environments. You can pass
``--environment`` as an argument to Acquia BLT to specify the correct
environmental configuration to load.


.. _blt-override-runtime:

Overriding variables at runtime
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You may overwrite a variable value at runtime by specifying the variable value
in your ``blt`` command using the argument ``syntax -D [key]=[value]``. For
example:

.. code-block:: bash

   blt tests:behat:run -D behat.tags='@mytags'

For configuration values that are indexed arrays, you can override individual
values using the numeric index, such as ``git.remotes.0``.

The following list includes some commonly customized Acquia BLT targets:

-  **artifact:\***

   -  **artifact:build**: To modify the behavior of the ``artifact:build``
      target, you may override Acquia BLT's ``deploy`` configuration. For
      example contents, review the ``deploy`` key in the `build.yml
      <https://github.com/acquia/blt/blob/HEAD/config/build.yml#L54>`__ file.

      More specifically, you can modify the build artifact using the
      following methods:

      -  Change which files are rsynced to the artifact by providing your
         own ``deploy.exclude_file`` value in the ``blt.yml`` file. For
         example contents, review the `upstream deploy-exclude.txt
         <https://github.com/acquia/blt/blob/HEAD/scripts/blt/deploy/deploy-exclude.txt>`__
         file:

         .. code-block:: yaml

              deploy:
                exclude_file: ${repo.root}/blt/deploy/rsync-exclude.txt

      -  If you want to add to the `upstream deploy-exclude.txt
         <https://github.com/acquia/blt/blob/HEAD/scripts/blt/deploy/deploy-exclude.txt>`__
         file instead of overriding it, you don't need to define your own
         ``deploy.exclude_file``. Instead, leverage the
         ``deploy-exclude-additions.txt`` file found under the top-level
         Acquia BLT directory by adding each file or directory you want to
         exclude on its own line. For example:

         .. code-block:: text

              /directorytoexclude
              excludeme.txt

      -  Change which files Git ignores in the artifact by providing your own
         ``deploy.gitignore_file`` value in the ``blt.yml`` file. For example
         contents, review the `upstream .gitignore
         <https://github.com/acquia/blt/blob/HEAD/scripts/blt/deploy/.gitignore>`__ file.

         .. code-block:: yaml

              deploy:
                gitignore_file: ${repo.root}/blt/deploy/.gitignore

      -  Execute a custom command after the artifact by providing your own
         ``command-hooks.post-deploy-build.dir`` and
         ``command-hooks.post-deploy-build.command`` values in the
         ``blt.yml`` file. For example:

         .. code-block:: yaml

              # Executed after deployment artifact is created.
              post-deploy-build:
                dir: ${deploy.dir}/docroot/profiles/contrib/lightning
                command: npm run install-libraries

         Or, :ref:`use a Robo hook in a custom file <blt-add-robo-hook>`.

         .. code-block:: text

              /**
                * This will be called after the artifact:build command.
                *
                * @hook post-command artifact:build
                */
               public function postArtifactBuild() {
                 $this->doSomething();
               }

   -  **Git hooks**: You may disable a Git hook by setting its value under
      ``git.hooks`` to false:

      .. code-block:: text

          git:
            hooks:
              pre-commit: false

      You can use a custom Git hook in place of Acquia BLT's default Git hooks
      by setting its value under ``git.hooks`` to the directory path
      containing the hook. The directory must contain an executable file
      named after the Git hook:

      .. code-block:: text

          git:
            hooks:
              pre-commit: ${repo.root}/my-custom-git-hooks

      In the preceding example, an executable file named ``pre-commit`` should
      exist in ``${repo.root}/my-custom-git-hooks``.

      Execute ``blt blt:init:git-hooks`` after modifying these values for
      changes to take effect. Also, most projects will already have a ``git``
      key in their ``blt.yml`` file; be sure to append hooks to this existing
      key.

   -  **commit-msg**: By default, Acquia BLT will execute the
      ``internal:git-hook:execute:commit-msg`` command when new Git commits
      are made. This command validates that the commit message matches the
      regular expression defined in ``git.commit-msg.pattern``. You can
      :ref:`override the default configuration <blt-modify-blt-config>`.

-  **tests:\***

   -  **tests:behat:run**: To modify the behavior of the ``tests:behat:run``
      target, you may override Acquia BLT's Behat configuration. For examples,
      review the `build.yml
      <https://github.com/acquia/blt/blob/HEAD/config/build.yml#L2>`__ file.

   -  **tests:phpcs:sniff:all**: To change the behavior of the
      ``tests:phpcs:sniff:all`` target, you may copy ``phpcs.xml.dist`` to
      ``phpcs.xml`` in your repository root directory, and then modify the
      XML. For more information, see the official `PHPCS documentation
      <https://github.com/squizlabs/PHP_CodeSniffer/wiki/Advanced-Usage#using-a-default-configuration-file>`__.

   -  **tests:twig:lint:all**: To prevent validation failures on any Twig
      filters or functions created in custom or contrib module
      ``twig.extension`` services, add filters and functions like the
      following:

      .. code-block:: text

          validate:
            twig:
              filters:
                - my_filter_1
                - my_filter_2
              functions:
                - my_function_1
                - my_function_2

.. Next review date 20200425
