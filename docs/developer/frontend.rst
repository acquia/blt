.. include:: ../../common/global.rst

Front-end development and Acquia BLT
====================================

Ideally, you will be using a theme that employs SASS or SCSS, a style guide,
and other front-end tools that require some type of build process.

Similar to Composer dependencies, your front-end dependencies and compiled
front-end assets should not be directly committed to the project repository.
Instead, they should be built during the creation of a production-ready
artifact.

Acquia BLT does not directly manage any of your front-end dependencies or
assets, but it does create opportunities for you to hook into the build
process with your own custom front-end commands. Additionally, Acquia BLT
ships with `Cog <https://github.com/acquia-pso/cog>`__, a base theme that
provides front end dependencies and front end build tasks compatible with
Acquia BLT.


.. _blt-available-target-hooks:

Available target hooks
----------------------

By default, Acquia BLT provides an opportunity for your front-end commands to
run at the following stages of the build process:

-  Install
-  Build
-  Test

Install
~~~~~~~

During the execution of ``blt setup`` and ``blt artifact:deploy``, Acquia BLT
will ``execute command-hooks.frontend-reqs.command``. This hook is intended
to provide an opportunity to install the tools required for your front-end
build process. For instance, you may use this hook to install dependencies
using NPM or Bower. For example:

.. code-block:: text

    command-hooks:
     frontend-reqs:
       dir: ${docroot}/themes/custom/[mytheme]
       command: 'npm install'

If you are using a sub-theme of Cog, executing ``npm install`` in your theme
directory will install all dependencies listed in `package.json
<https://github.com/acquia-pso/cog/blob/8.x-1.x/STARTERKIT/package.json>`__.

.. note::

   The ``npm install`` command does not generally install the same versions
   of packages from one run to the next, which is undesirable when testing
   and building for production. If you are deploying front-end assets, you
   may want to use ``npm ci`` instead of ``npm install`` to ensure that
   dependencies are installed consistently and deterministically. If
   Acquia BLT detects that build files (such as ``package-lock.json``) have
   changed during the build process, it may fail the deployment to prevent
   untested code from being deployed.

Build
~~~~~

During the execution of ``blt setup`` and ``blt artifact:deploy``, Acquia BLT
will execute ``command-hooks.frontend-assets.command``. This is always
executed after ``command-hooks.frontend-reqs.command``. This hook is intended
to provide an opportunity to compile your front-end assets, such as compiling
SCSS to CSS or generating a style guide.

.. code-block:: text

    command-hooks:
     frontend-assets:
       dir: ${docroot}/themes/custom/mytheme
       command: 'npm run build'

If you are using a sub-theme of Cog, executing ``npm run build`` in your theme
directory will execute the command defined in ``scripts.build`` in
`package.json
<https://github.com/acquia-pso/cog/blob/8.x-1.x/STARTERKIT/package.json#L51>`__.

Test
~~~~

During the execution of ``blt tests``, Acquia BLT will execute
``command-hooks.frontend-test.command``. This hook is intended to provide an
opportunity execute frontend tests, like JavaScript linting and visual
regression testing:

.. code-block:: text

    command-hooks:
     frontend-test:
       dir: ${docroot}/themes/custom/mytheme
       command: 'npm test'

If you are using a sub-theme of Cog, executing ``npm test`` in your theme
directory will execute the command defined in ``scripts.test`` in
`package.json
<https://github.com/acquia-pso/cog/blob/8.x-1.x/STARTERKIT/package.json>`__.

Executing complex commands
~~~~~~~~~~~~~~~~~~~~~~~~~~

If you need to execute something more complex, you can call a custom script
rather than directly embedding your commands in the YAML file, similar to the
following:

.. code-block:: text

    command-hooks:
     frontend-assets:
       dir: ${repo.root}
       command: ./scripts/custom/my-script.sh


.. _blt-front-end-system-requirements:

System requirements
-------------------

Strictly speaking, Acquia BLT does not have any system requirements for
front-end commands, as Acquia BLT does not provide any front-end commands
itself. Remember that Acquia BLT provides only an opportunity for you to
execute your own custom front-end commands. It is your responsibility to
determine and install your front-end system requirements, and to ensure that
the correct versions of prerequisite tools (including NPM and Bower) are
installed wherever you are running deploys (such as locally, in a virtual
machine, or in a continuous integration environment).

.. Next review date 20200422
