# Using BLT on Windows

BLT is supported on Windows 10, under the Windows Subsystem for Linux (Ubuntu Bash). You need to install this separately, and you must be running Windows 10 Anniversary edition or later, and the Windows installation must be 64-bit.

There are a few [known issues and quirks](#known-issues-and-quirks) with this approach.

## Install the Windows Subsystem for Linux

The Windows Subsystem for Linux allows you to use a full Linux environment via Bash on Ubuntu. This means you can install BLT's prerequisites on your Windows workstation in a similar way as you would on a Linux or Mac environment.

Follow Microsoft's official instructions to install [Bash on Ubuntu on Windows](https://msdn.microsoft.com/en-us/commandline/wsl/install_guide). Once that's done, you can install BLT's prerequisites, and then setup a new BLT project or work on an existing BLT project.

## Install PHP, Node.js, Git and Composer

  1. `sudo add-apt-repository ppa:ondrej/php` (hit 'Enter' when prompted)
  2. `sudo apt-get update`
  3. `sudo apt-get install -y php5.6-cli php5.6-curl php5.6-xml php5.6-mbstring php5.6-bz2 php5.6-gd php5.6-mysql mysql-client unzip git`
  4. `php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"`
  5. `php composer-setup.php`
  6. `sudo mv composer.phar /usr/local/bin/composer`
  7. `curl -sL https://deb.nodesource.com/setup_4.x | sudo -E bash -`
  8. `sudo apt-get install -y nodejs`

## Other Required setup

Before working with a BLT project, you need to configure Git correctly. Run the following commands to identify yourself to Git:

  1. `git config --global user.email "you@example.com"`
  2. `git config --global user.name "Your Name"`

And if you haven't already configured an SSH identity (useful for working with projects on GitHub and interacting with your sites on Acquia Cloud), you should [generate an SSH key](https://help.github.com/articles/generating-an-ssh-key/).

## Next steps

Your environment is now ready to [create a new](creating-new-project.md) BLT project, or use an [existing one](onboarding.md).

## Using Drupal VM

The Windows Subsystem for Linux isn't a full-fledged Linux operating system, rather an environment for running Linux apps that would normally run on Ubuntu 14.04. Therefore, [VirtualBox can't be installed in the WSL](http://askubuntu.com/a/816350/88829), and it's [unlikely Vagrant usage will be supported](https://github.com/mitchellh/vagrant/issues/7731)—though you _can_ install Vagrant in the WSL, using `dpkg -i` to install the [latest Vagrant `.deb` package download](https://releases.hashicorp.com/vagrant/).

Therefore, to use the prepackaged Drupal VM instance created by BLT through `vm init`, you should follow Drupal VM's Quick Start Guide to install VirtualBox and Vagrant, then you have two options for managing the VM:

  1. Use a separate PowerShell or other command line environment to manage the VM via `vagrant` commands.
  2. [Install cbwin](https://github.com/xilun/cbwin#installation) and use it to 'wrap' `vagrant` commands (e.g., `wrun vagrant up` to build the VM from inside Bash).

> Note that if you use `cbwin`, you will need to launch it's included `outbash.exe` environment (rather than the default Bash environment) so it can wrap calls to Windows executables. Also, you should make sure the BLT codebase is in a path accessible to both Windows and the WSL (e.g., `/mnt/c/Users/yourusername/Sites`), otherwise `vagrant` and other Windows apps won't be able to access the code.

After you run `vm init` (it may error out and say 'Virtualbox is missing is not installed' [sic]), you will then need to run commands pertaining to the VM manually, outside of BLT:

  - `wrun vagrant up` to start the VM
  - `wrun vagrant halt` to stop the VM
  - `wrun vagrant destroy -f` to delete the VM

## Known issues and quirks

As the WSL is a beta feature it is expected that some features may contain bugs or be incomplete.

At the time of writing these are the currently known issues which you may encounter.

  1. [Only portions of procfs are implemented, and there is limited inotify support](https://github.com/Microsoft/BashOnWindows/issues/216). This will impact things like Gulp where you commonly want to actively 'watch' for filesystem changes. In that particular instance you can use [gulp-watch](https://www.npmjs.com/package/gulp-watch) which polls periodically instead.
  2. [Network enumeration is not supported](https://github.com/Microsoft/BashOnWindows/issues/468). This will impact networking functions commonly required by popular frontend packages and utilities (e.g., Browsersync). There are workarounds discussed in the GitHub issue.
  3. [Permissions on /dev/tty are sometimes incorrect](https://github.com/Microsoft/BashOnWindows/issues/617). This can prevent ssh connectivity keyboard input cannot be read (required when entering a passphrase). A workaround is discussed in the GitHub issue.
  4. [Files created on Windows side are not visible on Linux side](https://github.com/Microsoft/BashOnWindows/issues/45). This can cause files modified by programs on Windows to be missing from the Windows Bash environment. For example, settings.php being generated by Acquia Dev Desktop and the file will be hidden from Bash on Windows.
