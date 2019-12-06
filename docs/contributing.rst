.. include:: ../common/global.rst

Contributing to Acquia BLT
==========================

Before you create an issue or pull request, Acquia recommends reading and
taking the time to understand the following guide. Submitted issues not based
on these guidelines may be closed.

Acquia BLT feature requests, bugs, support requests, and milestones are
tracked with the `BLT GitHub issue queue
<https://github.com/acquia/blt/issues>`__.

Note the branch statuses documented on the `GitHub page
<https://github.com/acquia/blt/tree/9.2.x>`__:

- Pull requests for enhancements will be accepted only for the active
  development branch (11.x and 10.x).

- Pull requests for bug fixes will be accepted only for supported
  branches (11.x, 10.x, and 9.2.x).


.. _blt-general-guidelines:

General guidelines
------------------

Note the following guidelines when submitting issues or pull requests:

- Issues filed directly with the Acquia BLT project aren't subject to a
  service-level agreement (SLA).

- Acquia BLT is distributed under the `GPLv2
  <https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html>`__ license; all
  documentation, code, and guidance is provided without warranty.

- The project maintainers are under no obligation to respond to support
  requests, feature requests, or pull requests.

- If more information is requested and no reply is received within a
  week, issues may be closed.

Newly filed issues will be reviewed by an Acquia BLT maintainer and added to
the *backlog* milestone if accepted.

BLT doesn't publish timelines or road maps to reflect when individual issues
will be addressed. If you would like to request prioritization of a specific
ticket, complete the following tasks:

- Submit product feedback through your Technical Account Manager or
  :ref:`submit a support ticket <contact-acquia-support>` on your Cloud
  subscription.

- Vote for the relevant issue by adding a ``+1`` reaction.

- Submit a pull request, which will receive priority review.


.. _blt-submitting-issues:

Submitting issues
-----------------

Before submitting an issue, be sure to search for existing issues (including
*closed* issues) matching your issue. Duplicate issues will be closed.

Use caution when selecting your issue type, and if you aren't sure of the issue
type, consider submitting a :ref:`support request <contact-acquia-support>`.

-  *Feature request*: A request for a specific enhancement for Acquia BLT.
   A feature request is distinct from a *bug report* because it indicates a
   missing feature for Acquia BLT instead of a literal error with Acquia BLT.
   Feature requests are distinct from support requests because they're
   specific and atomic requests for new Acquia BLT features, instead of
   a general request for help or guidance.

-  *Bug report*: A defined instance of Acquia BLT not behaving as expected. A
   bug report is distinct from a *feature request* because it represents a
   mismatch between what Acquia BLT does and what Acquia BLT claims to do. A
   bug report is distinct from a *support request* by including specific steps
   to reproduce the problem (ideally starting from a fresh installation of
   Acquia BLT) and justifying why the instance is a problem with Acquia BLT
   rather than with an underlying tool, such as `Composer
   <https://getcomposer.org/>`__ or `Drush <https://www.drush.org/>`__.

-  *Support request*: A request for help or guidance. Use the issue type if
   you aren't sure how to do something or can't find a solution to a problem
   that may or may not be a bug. Before filing a support request, review
   :doc:`BLT support </blt/support/>` for solutions to common problems and
   general troubleshooting techniques.

   If you have an Acquia subscription, consider :ref:`filing a support ticket
   <contact-acquia-support>` instead of an Acquia BLT issue to receive support
   subject to your SLA.

After selecting your issue type, be sure to complete the entire issue
template.


.. _blt-submitting-pull-requests:

Submitting pull requests
------------------------

Note the branch statuses documented on the `GitHub page
<https://github.com/acquia/blt>`__:

- Pull requests for enhancements will only be accepted for the active
  development branch (11.x or 10.x).

- Pull requests for bug fixes will only be accepted for supported
  branches (11.x, 10.x, or 9.2.x).

- When submitting a pull request for a bug fix or enhancement
  applying to several branches, submit only a single pull request
  to the latest development branch for review. A maintainer will
  backport the fix if appropriate.

Pull requests must also adhere to the following guidelines:

- Pull requests must be atomic and targeted at a single issue rather than
  broad scope.

- Pull requests must contain clear testing steps and justification, and
  all other information required by the pull request template.

- Pull requests must pass automated tests before they will be reviewed.
  Acquia recommends running the tests :ref:`locally <blt-developing-locally>`
  before submitting.

- Pull requests must meet Drupal coding standards and best practices as defined
  by the project maintainers.


.. _blt-developing-locally:

Developing Acquia BLT locally
-----------------------------

If you want to contribute to developing Acquia BLT, Acquia recommends
cloning Acquia BLT and linking it to a BLT-based project (referred to here as
``blted``) through Composer's path repository feature. BLT provides a command
``blt:dev:link-composer`` that does this for you and configures `Vagrant
<https://www.vagrantup.com/>`__ to use your development version of BLT.

Due to Vagrant restrictions for handling symlinks, Vagrant supports two types
of directory structures:

The first is to place BLT directly next to the project you are testing. For
instance:

- BLT is located at ``~/blt``
- Your BLT test project is located at ``~/blted``
- From your project (``~/blted``), run the following command:

  .. code-block:: text

     blt blt:dev:link-composer --blt-path=../blt

The second supported structure is to have BLT as a sibling once removed from
your test project, for example:

- BLT is located at ``~/packages/blt``
- Your BLT test project is located at ``~/sites/blted``
- From your project (``~/blted``), run the following command with no arguments
  (this is the default structure):

  .. code-block:: text

    blt blt:dev:link-composer

Your ``blted`` project will now have a `Composer <https://getcomposer.org/>`__
dependency on your local clone of Acquia BLT through a symlink. You can make
changes to files in ``blt`` and see them instantly reflected in
``blted/vendor/acquia/blt``.


.. _blt-testing:

Testing
-------

To complete the same release testing performed during continuous integration
(CI) execution, run the following command:

.. code-block:: bash

   ./vendor/bin/robo release:test

Acquia BLT version 10.x requires the following to run the same release testing
performed during CI execution:

- Four local MySQL databases available, with ``drupal``, ``drupal2``,
  ``drupal3``, and ``drupal4`` as the db names.

- A MySQL user with access to the four local MySQL databases with ``drupal``
  as the username and password which may be sensitive to the MySQL version. In
  newer versions of MySQL (8+), you may want to set the user password as
  follows:

  .. code-block:: text

      alter user 'drupal'@'localhost' identified with mysql_native_password by 'drupal';

- Enabling the PHP MySQL extension.

- `Chromedriver <https://chromedriver.chromium.org/>`__, `sqlite
  <https://www.sqlite.org/index.html>`__, and the `php-sqlite3
  <https://www.php.net/manual/en/book.sqlite3.php>`__ extension to run
  ``@group drupal`` tests.

- *(Optional)* Exclude ``@group requires-vm``.


.. Next review date 20200424
