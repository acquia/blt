.. include:: ../../common/global.rst

Using Acquia BLT on Windows
===========================

The information on this page describes both how to use Acquia BLT with
Windows 10, and it's :ref:`known issues <blt-windows-known-issues>`.


.. _blt-windows-editing-files:

Editing files in WSL and Windows
--------------------------------

By default, when you start WSL the current directory is ``/home/your_user``.
Do not create files in this directory that you also want to edit on Windows.
Although these files are accessible from Windows, it's `dangerous to edit
them
<https://blogs.msdn.microsoft.com/commandline/2016/11/17/do-not-change-linux-files-using-windows-apps-and-tools/>`__.
Instead, change directory to your Windows user's home directory, with a
command similar to the following:

.. code-block:: bash

     cd /mnt/c/Users/John


.. _blt-windows-using-drupal-vm:

Using Drupal VM
---------------

The Windows Subsystem for Linux isn't a full-fledged Linux operating system;
instead it is an environment for running Linux apps that would normally run
on Ubuntu 14.04. Therefore, `VirtualBox can't be installed in the
WSL <http://askubuntu.com/a/816350/88829>`__, and it's `unlikely Vagrant
usage will be supported <https://github.com/mitchellh/vagrant/issues/7731>`__
(although you *can* install Vagrant in the WSL, using ``dpkg -i`` to install
the `latest Vagrant .deb package download
<https://releases.hashicorp.com/vagrant/>`__).

To use the prepackaged Drupal VM instance created by Acquia BLT through
``vm init``, follow Drupal VM's Quick Start Guide to install VirtualBox and
Vagrant. You will then have two options for managing the virtual machine (VM):

-  Use a separate PowerShell or other command-line environment to manage the
   VM with ``vagrant`` commands.
-  `Install cbwin <https://github.com/xilun/cbwin#installation>`__ and
   use it to *wrap* ``vagrant`` commands (for example, ``wrun vagrant up`` to
   build the VM from inside of Bash).

.. note::

   If you use ``cbwin``, you will need to launch its included ``outbash.exe``
   environment (rather than the default Bash environment) so it can wrap calls
   to Windows executables. Also, you should make sure the Acquia BLT codebase
   is in a path accessible to both Windows and the WSL (for example,
   ``/mnt/c/Users/yourusername/Sites``), otherwise ``vagrant`` and other
   Windows apps won't be able to access the code.

After you run ``vm init`` (it may display an error message stating *Virtualbox
is missing is not installed* [sic]), you will then need to run commands
pertaining to the VM manually, outside of Acquia BLT:

-  ``wrun vagrant up``: Start the VM.
-  ``wrun vagrant halt``: Stop the VM.
-  ``wrun vagrant destroy -f`` – Delete the VM.


.. _blt-windows-known-issues:

Known issues
------------

Due to the WSL being in beta, it is expected that some features may contain
bugs or be incomplete.

These are the currently known issues which you may encounter:

-  `Only portions of procfs are implemented, and there is limited inotify
   support <https://github.com/Microsoft/BashOnWindows/issues/216>`__. |br|
   This will impact things like Gulp where you commonly want to actively
   'watch' for filesystem changes. In that particular instance you can use
   `gulp-watch <https://www.npmjs.com/package/gulp-watch>`__ which polls
   periodically instead.
-  `Network enumeration is not supported
   <https://github.com/Microsoft/BashOnWindows/issues/468>`__. |br|
   This will impact networking functions commonly required by popular
   front-end packages and utilities (for example, Browsersync). There are
   workarounds discussed in the GitHub issue.
-  Permissions on ``/dev/tty`` `are sometimes incorrect
   <https://github.com/Microsoft/BashOnWindows/issues/617>`__. |br|
   This can prevent ssh connectivity keyboard input cannot be read
   (required when entering a passphrase). The GitHub issue includes
   discussions regarding a workaround.

.. Next review date 20200422
