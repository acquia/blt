.. include:: ../common/global.rst

Contributing to Acquia BLT
==========================

Before you create an issue or pull request, read and take the time to
understand this guide. Submitted issues that are not based on these
guidelines may be closed.

Acquia BLT feature requests, bugs, support requests, and milestones are
tracked with the `BLT GitHub issue queue
<https://github.com/acquia/blt/issues>`__.

Note the branch statuses documented on the `GitHub page
<https://github.com/acquia/blt/tree/9.2.x>`__:

- Pull requests for enhancements will be accepted only for the active
  development branch (11.x and 10.x).

- Pull requests for bug fixes will be accepted only for supported
  branches. (11.x, 10.x, and 9.2.x)


.. _blt-general-guidelines:

General guidelines
------------------

Note the following when submitting issues or pull requests:

- Issues filed directly to the Acquia BLT project are not subject to a
  service-level agreement (SLA).

- Acquia BLT is distributed under the GPLv2 license; all documentation, code,
  and guidance is provided without warranty.

- The project maintainers are under no obligation to respond to support
  requests, feature requests, or pull requests.

- If additional information is requested and no reply is received within a week,
issues may be closed.

Newly-filed issues will be reviewed by an Acquia BLT maintainer and added to the "backlog" milestone if accepted.

BLT does not publish timelines or roadmaps as to when individual issues will be addressed. If you would like to request that a specific ticket be prioritized, please do one or more of the following:

- Submit product feedback via your Technical Account Manager or a Support ticket on your Cloud subscription.

- Upvote the relevant issue by adding a `+1` reaction.

- Submit a pull request, which will receive priority review.


.. _blt-submitting-issues:


Submitting issues
-----------------

Before submitting an issue, be sure to search for existing issues (including **closed** issues) that might match your issue. Duplicate issues will be closed.

Select your issue type carefully, and if you are not sure of the issue type,
the issue type is probably a support request.

-  *Feature request*: A request for a specific enhancement for Acquia BLT.
   This is distinct from a *bug report*, as it indicates a missing feature
   for Acquia BLT functionality, instead of a literal error with Acquia BLT.
   Feature requests are distinct from support requests, in that they are
   specific and atomic requests for new Acquia BLT functionality, instead of
   a general request for help or guidance.

-  *Bug report*: A clearly defined instance of Acquia BLT not behaving as
   expected. It is distinct from a *feature request* in that it
   represents a mismatch between what Acquia BLT does and what
   Acquia BLT claims to do. It is distinct from a *support
   request* by having specific steps to reproduce the problem (ideally
   starting from a fresh installation of Acquia BLT) and
   justification as to why this is a problem with Acquia BLT rather than an
   underlying tool, such as `Composer <https://getcomposer.org/>`__ or `Drush
   <https://www.drush.org/>`__.

-  *Support request*: A request for help or guidance. Use this issue type if
   you are not sure how to do something or can't find a solution to a problem
   that may or may not be a bug. Before filing a support request, review the
   :doc:`FAQ </blt/faq/>` for solutions to common problems and general
   troubleshooting techniques.

   If you have an Acquia subscription, consider :ref:`filing a Support ticket
   <contact-Acquia-Support>` instead of an Acquia BLT issue to receive support
   subject to your SLA.

After selecting your issue type, be sure to complete the entire issue
template.

.. _blt-submitting-pull-requests:

Submitting pull requests
------------------------

Note the branch statuses documented on the `GitHub page
<https://github.com/acquia/blt>`__:

- Pull requests for enhancements will only be accepted for the active
  development branch. (11.x or 10.x)

- Pull requests for bug fixes will only be accepted for supported
  branches. (11.x, 10.x, or 9.2.x)

- When submitting a pull request for a bug fix or enhancement
  that may apply to multiple branches, submit only a single pull request
  to the latest development branch for review. A maintainer will
  backport the fix if appropriate.

Pull requests must also adhere to the following guidelines:

- Pull requests must be atomic and targeted at a single issue rather
        than broad-scope.

- Pull requests must contain clear testing steps and justification, as
        well as all other information required by the pull request template.

- Pull requests must pass automated tests before they will be reviewed.
  Acquia recommends running the tests
  :ref:`locally<blt-developing-locally>` before submitting.

- Pull requests must comply with Drupal coding standards and best
  practices as defined by the project maintainers.

.. _blt-developing-locally:

Developing Acquia BLT locally
-----------------------------

If you want to contribute by actively developing Acquia BLT, we recommend
cloning Acquia BLT and linking it to a BLT-based project (referred to here as `blted`) via Composer's path repository functionality. BLT provides a command `blt:dev:link-composer` that does this for you and additionally configures Vagrant to use your development version of BLT.

Due to restrictions in how Vagrant handles symlinks, only two types of directory structures are currently supported.

The first is to have BLT directly adjacent to the project you are testing. For instance:
- BLT is located at `~/blt`
- Your BLT test project is located at `~/blted`
- From your project (`~/blted`), run `blt blt:dev:link-composer --blt-path=../blt`

The second supported structure is to have BLT as a sibling once removed from your test project, e.g.:
- BLT is located at `~/packages/blt`
- Your BLT test project is located at `~/sites/blted`
- From your project (`~/blted`), run `blt blt:dev:link-composer` with no arguments (this is the default structure).

Your `blted` project will now have a Composer dependency on your local clone of Acquia BLT via a symlink. You can therefore make changes to files in `blt` and see them immediately reflected in `blted/vendor/acquia/blt`.

.. _blt-testing:

Testing
-------

To complete the same release testing that is performed during continuous
integration (CI) execution, run the following command:

.. code-block:: bash

   ./vendor/bin/robo release:test

Note that Acquia BLT version 10.x requires the following to run the same
release testing performed during CI execution:

- Four local MySQL databases available, with ``drupal``, ``drupal2``,
  ``drupal3``, and ``drupal4`` as the db names.

- A MySQL user with access to the above, with ``drupal`` as the username and
  password. It may be sensitive to MySQL version. In newer versions of
  MySQL (8+), you may need to set the user password as follows:

  .. code-block:: text

      alter user 'drupal'@'localhost' identified with mysql_native_password by 'drupal';

- The PHP MySQL extension to be enabled.

- Chromedriver, sqlite, and the php-sqlite3 extension to run
  ``@group drupal`` tests.

- *(Optional)* Exclude ``@group requires-vm``.

PHPUnit
~~~~~~~

For information about PHPUnit, see :doc:`the PHPUnit section of the
automated testing docs </blt/developer/testing/>`.

.. Next review date 20200424
