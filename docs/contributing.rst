.. include:: ../common/global.rst

Contributing to Acquia BLT
==========================

Before you create an issue or pull request, read and take the time to
understand this guide. Submitted issues that are not based on these
guidelines may be closed.

.. tabs::

   .. group-tab:: 10.x

      Acquia BLT feature requests, bugs, support requests, and milestones are
      tracked with the `BLT GitHub issue queue
      <https://github.com/acquia/blt/issues>`__.

   .. group-tab:: 9.2.x

      Acquia BLT work is tracked in the `BLT GitHub issue queue
      <https://github.com/acquia/blt/issues>`__ and organized
      on a `Waffle.io Kanban Board <https://waffle.io/acquia/blt>`__.

      Note the branch statuses documented in the `README
      <https://blt.readthedocs.io/en/latest/README/>`__ and `GitHub page
      <https://github.com/acquia/blt/tree/9.2.x>`__:

      - Pull requests for enhancements will be accepted only for the active
        development branch.

      - Pull requests for bug fixes will be accepted only for supported
        branches.


.. _blt-submitting-issues:

Submitting issues
-----------------

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

Newly-filed issues will be reviewed by an Acquia BLT maintainer. If
additional information is requested and no reply is received within a week,
issues may be closed.

Note the following when submitting issues:

- Issues filed directly to the Acquia BLT project are not subject to a
  service-level agreement (SLA).

- Acquia BLT is distributed under the GPLv2 license; all documentation, code,
  and guidance is provided without warranty.

- The project maintainers are under no obligation to respond to support
  requests, feature requests, or pull requests.


.. _blt-submitting-pull-requests:

Submitting pull requests
------------------------

.. tabs::

   .. group-tab:: 10.x

      Note the branch statuses documented in the `README
      <https://blt.readthedocs.io/en/latest/README/>`__ and `GitHub page
      <https://github.com/acquia/blt>`__:

      - Pull requests for enhancements will only be accepted for the active
        development branch.

      - Pull requests for bug fixes will only be accepted for supported
        branches.

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

      Pull requests will be reviewed by a Acquia BLT maintainer and are not
      subject to an SLA. If additional information or work is requested and no
      reply is received within a week, pull requests may be closed.

   .. group-tab:: 9.2.x

      Changes should be submitted as Github pull requests to the project
      repository. To help with review, pull requests are expected to adhere
      to two main guidelines:

      - Pull requests should be atomic and targeted at a single issue rather
        than broad-scope.

      - Pull requests are expected to follow the template defined by the
        project in the `Github issue template
        <https://github.com/acquia/blt/blob/9.2.x/.github/ISSUE_TEMPLATE.md>`__.


.. _blt-developing-locally:

Developing Acquia BLT locally
-----------------------------

If you want to contribute by actively developing Acquia BLT, we recommend
cloning Acquia BLT and also creating an Acquia BLT-based project for testing
your changes.

Use the following commands to create a testable Acquia BLT-created project
alongside Acquia BLT:

.. code-block:: text

   git clone https://github.com/acquia/blt.git
   rm -rf blted8
   composer install --working-dir=blt
   cd blt
   ./vendor/bin/robo create:from-symlink

.. important::

   The following information applies to Acquia BLT version 9.2.x.

   Although you are not required to have `Ansible
   <https://github.com/ansible/ansible>`__ installed on your host computer to
   use Acquia BLT, you must install Ansible on your host to boot the
   virtual machine in the ``blted8`` project which the preceding command
   creates.

The new ``blted8`` directory will have a Composer dependency on your local
clone of Acquia BLT using a ``../blt`` symlink. You can make changes to files
in ``blt`` and see them immediately reflected in ``blted8/vendor/acquia/blt``.


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
