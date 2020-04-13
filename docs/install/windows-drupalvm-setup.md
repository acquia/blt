# Using BLT and DrupalVM on a windows machine

## Preliminary setup
***
You will need to install the following apps on your windows machine if you have not done so already.
* Install the [Windows Subsystem for Linux with Ubuntu Bash](https://docs.microsoft.com/en-us/windows/wsl/install-win10).
* From the Microsoft store install Ubuntu
![](https://docs.microsoft.com/en-us/windows/wsl/media/store.png)
* Install [Vagrant](https://www.vagrantup.com/downloads.html).
* Install [VirtualBox](https://www.virtualbox.org/wiki/Downloads).
* Install [Cmder](https://cmder.net/)
* Open the Ubuntu bash.
 1. Run `sudo apt-get update sudo apt-get upgrade`
 2. Install php.
    * `sudo add-apt-repository ppa:ondrej/php`
    * `sudo apt-get install -y php7.2-cli php7.2-curl php7.2-xml php7.2-mbstring php7.2-bz2 php7.2-gd php7.2-mysql mysql-client unzip git`
    * Run `php -v` and make sure you see version 7.2 listed
    * `sudo apt-get install php7.2-soap`
 3. Install composer.
    * `php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"`
    * `php composer-setup.php`
    * `sudo mv composer.phar /usr/local/bin/composer`
 4. Clone you github repo
   
## Drupal VM Setup
***
1. Review the [Required / Recommended Skills](https://docs.acquia.com/blt/developer/skills/) for working with a BLT project.
2. Ensure that your computer meets the minimum installation requirements (and then install the required applications). See the [System Requirements](https://docs.acquia.com/blt/install/).
3. Run Cmder as an admin
4. You will need to cd into the directory where you cloned the site locally.
  * Note that Ubuntu Bash's home directory is located in your Windows user's home directory, in a path like \Users\[username]\AppData\Local\Packages\CanonicalGroupLimited.UbuntuonWindows_79rhkp1fndgsc\LocalState\rootfs\home\[ubuntu-username\
5. Once inside the site root directory run composer install
6. Install vagrant plugins.
  * `export VAGRANT_WSL_ENABLE_WINDOWS_ACCESS="1"`
  * `vagrant plugin install vagrant-vbguest`
  * `vagrant plugin install vagrant-hostsupdater`
7. Run vagrant up
8. After setup completes run vagrant ssh to ssh into the VM. At this point you should be able to do your development work from within the VM.
## From within the VM
9. To ensure that upstream changes to the parent repository may be tracked, add the upstream locally to the VM as well.
  - git remote add upstream <github_repo.git>
10. Config git.
   * `git config --global user.name "John Doe"`
   * `git config --global user.email johndoe@example.com`
11. Run the initial setup:
   * `blt setup`
12. Access the site and do necessary work at [http://local.yoursite.com](http://local.yoursite.com) by running this:
   1. `cd docroot`
   2. `drush uli`
Additional [BLT documentation](https://docs.acquia.com/blt/) may be useful. You may also access a list of BLT commands by running `blt` from the command line.

# Useful VM Commands
***
  * `vagrant halt` - Shut down the VM
  * `vagrant up` - Start the VM
  * `vagrant destroy` - Remove the VM
  * `vagrant ssh` - Log into the VM
  * `vagrant provision` - Run after making changes to the config.yml located in the box directory to incorporate new changes.
