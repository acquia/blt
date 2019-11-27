.. include:: ../../common/global.rst

Code review
===========

This documentation page provides guidance for performing a review of another
developer's code. The code review process must occur on GitHub through a pull
request (PR). For information about how to submit pull requests, and how they
fit into the development workflow, see :doc:`/blt/developer/dev-workflow/`.

Code review is both an art and a science. You must review all code merged into
a project.

You must ensure the code meets the established standards. The code reviewer
must consider whether the code accomplishes the work in the best way given the
project priorities and constraints.


.. _blt-code-review-considerations:

Code review considerations
--------------------------

The following examples illustrate the major considerations a code reviewer
must make and include several high level examples for each:

-  *Purpose and scope*: Does the code do the right things?

   -  Does the code meet the requirements of the ticket?
   -  Does the code affect only what needs changed for the scope
      of the ticket?
   -  How can you check functional changes?

-  *Implementation*: Does the code achieve its goal in the right manner?

   -  Is the code in the right place?
   -  Does the code leverage the correct APIs and variables as expected?
      Common issues:

      -  Use of global ``$language``, ``LANGUAGE_NONE`` instead of ``und``.
      -  Use of ``t()``.

   -  Does the code follow basic code principles?

      -  Ensure functions are logically atomic with low cyclomatic complexity.
      -  Ensure logic performs at the correct layer, such as no logic in the
         presentation layer.
      -  Determine that all code components are re-usable.

   -  Ensure you are using best practices:

      -  Views
      -  Features
      -  Configuration updates

-  *Code style and standards*: Does the code meet `Drupal coding standards
   <https://www.drupal.org/coding-standards>`__ and stylistic expectations?

   -  You have validated all code using the `Coder
      <https://www.drupal.org/project/coder>`__ module.
   -  Note the Drupal coding standards for the following items:

      -  `PHP <https://www.drupal.org/coding-standards>`__
      -  `PHP OOP <https://www.drupal.org/node/608152>`__
      -  `SQL <https://www.drupal.org/node/2497>`__
      -  `JS <https://www.drupal.org/node/172169>`__
      -  `Twig <https://www.drupal.org/node/1823416>`__
      -  `CSS <https://www.drupal.org/coding-standards/css>`__
      -  `HTML <https://groups.drupal.org/node/6355>`__
      -  `YML <https://www.drupal.org/coding-standards/config>`__

   -  You have named all classes, properties, and methods logically and
      consistently.

-  *Security*: Does the code meet `Drupal security best practices
   <https://www.drupal.org/docs/8/security>`__?

   -  Ensure your code uses Drupal security best practices:

      -  Prevent `XSS and SQL Injection
         <https://www.drupal.org/docs/8/security/writing-secure-code-for-drupal-8>`__.
      -  `Sanitize output
         <https://www.drupal.org/docs/8/security/drupal-8-sanitizing-output>`__.
      -  Prevent `CSRF attacks <https://www.drupal.org/node/178896>`__.

   -  Determine that any contributed modules added have stable releases and do
      not have outstanding `security advisories
      <https://www.drupal.org/security/contrib>`__.

-  *Performance*: How does the code impact website performance?

   -  Code must use caching whenever possible.

      -  Caution with using ``$_SESSION``, which invalidates page cache.

   -  Code must not be needlessly expensive.

      -  Caution with full node or entity loads in loops.

         -  Use of `Entity API <https://www.drupal.org/project/entity>`__,
            such as ``entity_metadata_wrapper()`` as a way to access and
            traverse entity properties and fields. Ensure you wrap usages in
            ``try { ... } catch (EntityMetadataWrapperException $e) { ... }``.

      -  Caution with ``hook_init()`` and ``hook_boot()``.

-  *Test coverage*: Does the pull request include required automated
   tests?

   -  All application features must be covered by a functional test through
      either Behat or PHPUnit.
   -  All custom libraries must be covered using unit tests through PHPUnit.

-  *Documentation*: Does the code meet Drupal Coding Standard minimum
   documentation requirements?

   -  The code must be self-documenting. For more information, see `Code Tells
      You How, Comments Tell You Why
      <http://blog.codinghorror.com/code-tells-you-how-comments-tell-you-why/>`__.
   -  Include more user-facing documentation where necessary.

-  *Configuration management*: Is all configuration managed in code?

   -  You must manage all configuration in code. Databases are never
      pushed upstream.
   -  You must manage all required configuration changes in code through
      update hooks. In most cases, the Release Master must not run anything
      beyond ``drush updb`` when running a release.


.. _blt-code-review-resources:

Resources
---------

-  `A Quick Guide for Code Reviews
   <https://www.lullabot.com/articles/a-quick-guide-for-code-reviews>`__
-  `How to review Drupal code
   <http://colans.net/blog/how-review-drupal-code>`__

.. Next review date 20200418
