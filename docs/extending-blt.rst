.. include:: ../common/global.rst

:orphan:

Extending and overriding Acquia BLT
===================================

Acquia BLT uses `Robo <https://github.com/consolidation/Robo>`__ to provide
commands.


.. _blt-add-robo-hook:

Adding a custom Robo hook or command
------------------------------------

Robo uses the `Annotated Command
<https://github.com/consolidation/annotated-command>`__ library to enable you
to add commands and hook into existing BLT commands. This allows you
to execute custom code in response to various events, typically before
or after executing a BLT command.

For a list of all available hook types, see `Annotated Command's hook types
<https://github.com/consolidation/annotated-command#hooks>`__.

To create your own Robo PHP command or hook, complete the following steps:

#.  Create a new file in ``blt/src/Blt/Plugin/Commands`` named using the
    pattern ``*Commands.php``. The file naming convention is required. You
    can also provide custom commands in a separate Composer package if it
    exposes the commands with PSR4.

    You must use the namespace ``Example\Blt\Plugin\Commands`` in your
    command file.

#.  Generate an example command file by running the following command:

    .. code-block:: bash

       blt example:init

    You may use the generated file as a guide for writing your own command.

#.  Follow the `Robo PHP Getting Started guide
    <http://robo.li/getting-started/#commands>`__ to write a custom command.


.. _blt-replacing-overriding-robo-command:

Replacing or overriding a Robo command
--------------------------------------

To replace an Acquia BLT command with your own custom version, implement the
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

To modify the behavior of PHPCS, see the `tests:phpcs:sniff:all
<https://blt.readthedocs.io/en/latest/extending-blt/#testsphpcssniffall>`__
documentation.

To modify the filesets that are used in other commands (such as
``tests:twig:lint:all``, ``tests:yaml:lint:all``, and ``tests:php:lint``),
complete the following steps:

#.  Generate an example ``Filesets.php`` file by running the following
    command:

    .. code-block:: bash

       blt example:init

    You may use the generated file as a guide for writing your own fileset.

#.  Create a public method in the ``Filesets`` class in the generated file.
#.  Add a Fileset annotation to your public method, specifying its id:

    .. code-block:: text

          @fileset(id="files.yaml.custom")

#.  Instantiate and return a ``Symfony\Component\Finder\Finder`` object. The
    files found by the finder comprise the fileset.

You can use the Fileset id in various configuration values in your
``blt/blt.yml`` file. For example, you may modify ``tests:yaml:lint:all`` to
scan only your custom fileset by adding the following information to the
``blt/blt.yml`` file:

.. code-block:: yaml

      validate:
        yaml:
          filesets:
            - files.yaml.custom


.. _blt-modify-blt-config:

Modifying your Acquia BLT configuration
---------------------------------------

You can customize your Acquia BLT configuration by overriding the value of
default variable values. The `build.yml
<https://github.com/acquia/blt/blob/9.x/config/build.yml>`__ file contains
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

The following list includes some of the more commonly customized Acquia BLT
targets:

*  **artifact:\***

   -  **artifact:build**: To modify the behavior of the ``artifact:build``
      target, you may override Acquia BLT's ``deploy`` configuration. For
      example contents, review the ``deploy`` key in the `build.yml
      <https://github.com/acquia/blt/blob/9.x/config/build.yml#L54>`__ file.

      More specifically, you can modify the build artifact using the
      following methods:

      -  Change which files are rsynced to the artifact by providing your
         own ``deploy.exclude_file`` value in the ``blt.yml`` file. For
         example contents, review the `upstream deploy-exclude.txt
         <https://github.com/acquia/blt/blob/9.x/scripts/blt/deploy/deploy-exclude.txt>`__
         file:

         .. code-block:: yaml

              deploy:
                exclude_file: ${repo.root}/blt/deploy/rsync-exclude.txt

      -  If you want to add to the `upstream deploy-exclude.txt
         <https://github.com/acquia/blt/blob/9.x/scripts/blt/deploy/deploy-exclude.txt>`__
         file instead of overriding it, you need not define your own
         ``deploy.exclude_file``. Instead, leverage the
         ``deploy-exclude-additions.txt`` file found under the top-level
         Acquia BLT directory by adding each file or directory you want to
         exclude on its own line. For example:

         .. code-block:: text

              /directorytoexclude
              excludeme.txt

      -  Change which files are gitignored in the artifact by providing your
         own ``deploy.gitignore_file`` value in the ``blt.yml`` file. For
         example contents, review the `upstream .gitignore
         <https://github.com/acquia/blt/blob/9.x/scripts/blt/deploy/.gitignore>`__
         file.

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
      containing of the hook. The directory must contain an executable file
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

*  **tests:\***

   -  **tests:behat:run**: To modify the behavior of the ``tests:behat:run``
      target, you may override Acquia BLT's Behat configuration. For examples,
      review the `build.yml
      <https://github.com/acquia/blt/blob/9.x/config/build.yml#L2>`__ file.

   -  **tests:phpcs:sniff:all**: To modify the behavior of the
      ``tests:phpcs:sniff:all`` target, you may copy ``phpcs.xml.dist`` to
      ``phpcs.xml`` in your repository root directory, and then  modify the
      XML. For more information, see the official `PHPCS documentation
      <https://github.com/squizlabs/PHP_CodeSniffer/wiki/Advanced-Usage#using-a-default-configuration-file>`__.

   -  **tests:twig:lint:all**: To prevent validation failures on any Twig
      filters or functions created in custom or contrib module
      ``twig.extension`` services, add filters and functions similar to the
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
