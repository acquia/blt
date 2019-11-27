.. include:: ../../common/global.rst

Git configuration
=================

Acquia BLT provides `Git hooks
<https://git-scm.com/book/en/v2/Customizing-Git-Git-Hooks>`__ that must be
symlinked into your local repository's ``.git`` directory, using the
``blt:init:git-hooks`` task during the :doc:`onboarding process
</blt/developer/onboarding/>`.

These hooks should be used on all projects, as they will save developers time.
In particular, the pre-commit hook will prevent a Git commit if validation
fails on the code being committed (which will also occur during
``blt:validate`` calls during continuous integration).


.. _blt-provided-hooks:

Provided Hooks
--------------

Acquia BLT provides the following default hooks for your use:

-  *commit-msg*: Validates the syntax of a Git commit message before it is
   committed locally.
-  *pre-commit*: Runs Drupal Code Sniffer on committed code before it is
   committed locally.

.. Next review date 20200422
