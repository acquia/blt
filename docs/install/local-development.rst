.. include:: ../../common/global.rst

Local development with Acquia BLT
=================================

Acquia recommends the use of one of the following solutions for local
development:

-  :ref:`Drupal VM <blt-drupal-vm-blt-projects>`: An isolated virtual
   machine (VM), built with Vagrant and Ansible.
-  :ref:`Acquia Dev Desktop
   <blt-dev-desktop-blt-projects>`: A turn-key LAMP stack
   tailored specifically for Acquia-hosted Drupal websites.
-  :ref:`Alternative local development environments
   <blt-alt-dev-env>`

Regardless of the local environment you select, use the following guidelines:

-  To guarantee similar behavior, use Apache as your web server.
-  If you host your project on Acquia Cloud, be sure to match
   :doc:`our software versions </acquia-cloud/arch/tech-platform/>`.

Acquia developers use `PHPStorm <http://www.jetbrains.com/phpstorm/>`__ and
recommend it for local development environments. Acquia has written several
`Knowledge Base articles <https://support.acquia.com/>`__ about using
PHPStorm for Drupal development.


.. _blt-drupal-vm-blt-projects:

Using Drupal VM for Acquia BLT-generated projects
-------------------------------------------------

To use `Drupal VM <http://www.drupalvm.com/>`__ with a Drupal project
that is generated with Acquia BLT:

#.  Download the Drupal VM dependencies listed in `Drupal VM's README
    <https://github.com/geerlingguy/drupal-vm#quick-start-guide>`__. If you
    are running `Homebrew <http://brew.sh/index.html>`__ on macOS, you can
    use the following commands:

    .. code-block:: bash

         brew tap caskroom/cask
         brew install php71 git composer ansible drush
         brew cask install virtualbox vagrant

#.  Create and boot the VM:

    .. code-block:: bash

         blt vm

#.  Commit all resulting files, including ``box/config.yml`` and
    ``Vagrantfile``.

    .. code-block:: bash

         git add -A
         git commit -m <your commit meessage>

#.  Install Drupal and complete your Acquia BLT installation:

    .. code-block:: bash

         vagrant ssh
         blt setup

#.  Sign in to Drupal:

    .. code-block:: bash

         drush uli

You can make other changes if you choose to match the Acquia
Cloud server configuration more closely. See Drupal VM's example configuration
changes in Drupal VM's ``examples/acquia/acquia.overrides.yml`` file.

Subsequently, you should use ``vagrant`` commands to interact with the VM. Do
not re-run ``blt vm``. For instance, use ``vagrant up`` to start the VM, and
then run ``vagrant halt`` to stop it.

.. note::

    With a Drupal VM installation, Acquia BLT expects all commands (with the
    exception of commands in the ``blt vm`` namespace), to be executed in the
    VM. To SSH into the VM, run ``vagrant ssh`` as you did in the
    "Install Drupal" previous step.

Drupal VM and Behat tests
~~~~~~~~~~~~~~~~~~~~~~~~~

The Drupal Extension for Behat has an `inherent limitation
<https://behat-drupal-extension.readthedocs.io/en/3.1/drivers.html>`__:
it cannot use the 'drupal' driver to bootstrap Drupal on a remote
server. If you're using Drupal VM and would like to execute Behat tests
using the 'drupal' driver, you must execute them from within the VM.
This is a break of the established pattern of running all BLT commands
outside of the VM.

To execute Behat tests using the 'drupal' driver on a Drupal VM
environment, you must do the following:

#. SSH into the VM ``vagrant ssh``
#. Execute behat tests ``blt tests:behat:run``

Alternately, you may choose to write only behat tests that utilize the
Drupal Extension's "drush" driver. Doing this will allow you to run
``blt tests:behat:run`` from the host machine.


.. _blt-dev-desktop-blt-projects:

Using Acquia Dev Desktop for BLT-generated projects
---------------------------------------------------

Project creation and installation changes
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

#.  Add a new website in :doc:`/dev-desktop/` by clicking **Import local
    Drupal site**. Point it at the ``docroot`` directory in your new codebase.
    Your ``/sites/default/settings.php`` file will be modified to include the
    Acquia Dev Desktop database connection information.

#.  Follow the normal setup process by running the following command:

    .. code-block:: bash

         blt setup

Drush support
~~~~~~~~~~~~~

To use a custom version of Drush (required by Acquia BLT) with Acquia Dev
Desktop, you must complete the following steps:

#.  Add the following lines to ``~/.bash_profile`` (or equivalent file):

    .. code-block:: text

         export PATH="/Applications/DevDesktop/mysql/bin:$PATH"
         export DEVDESKTOP_DRUPAL_SETTINGS_DIR="$HOME/.acquia/DevDesktop/DrupalSettings"

#.  Ensure that Acquia Dev Desktop's PHP binary is being used on the CLI. This
    will require adding a line like the following to your ``~/.bash_profile``:

    .. code-block:: text

         export PATH=/Applications/DevDesktop/php7_0/bin:$PATH

    The exact line will depend upon the version of PHP that you intend to use.
    You can determine the effect of the value with the ``which php`` command.

#.  Enable the usage of environmental variables by adding the following line
    to ``php.ini``, which you can locate with ``php --ini``:

    .. code-block:: text

         variables_order = "EGPCS"

#.  Restart your terminal session.

#.  Optionally, run the following command to check your configuration:

    .. code-block:: text

         blt doctor


.. _blt-alt-dev-env:

Alternative local development environments
------------------------------------------

Some teams may prefer to use a different development environment. Drupal
VM offers flexibility and a uniform configuration, but sometimes a tool
(such as Acquia Dev Desktop, MAMP or XAMPP, or a bespoke Docker-based dev
environment) may be preferable.

It's up to each team to choose how to handle local development, but some main
factors that help a project's velocity with local development include the
following:

-  Uniformity and the same configuration across all developer
   environments.
-  Ease of initial environment configuration.
-  Ability to emulate all aspects of the production environment with
   minimal hassle (for example, Varnish, Memcached, Solr, Elasticsearch,
   and different PHP versions).
-  Helpful, built-in developer tools (for example, XHProf, Xdebug, Adminer,
   or PimpMyLog).
-  Ease of use across Windows, Mac, and Linux workstations.

If you select a different solution than recommended here, be sure it meets
all your team's and project's needs, and won't be a hindrance to project
development velocity.

Although Acquia can't officially support these alternative environments,
feel free to submit documentation of any special steps necessary with the tool
you choose so that others can learn from your experience. Other users have
contributed tips to the following environment:

-  :doc:`Lando </blt/install/alt-env/lando/>`

.. Next review date 20200422
