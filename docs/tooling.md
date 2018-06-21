The following is a list of Operating System level tools that BLT uses.

Not all of these are absolutely required. Some are required only when using certain features of BLT, like Selenium-based testing or Drupal VM integration. 

### System-level packages

Please see [System Requirements](INSTALL.md/#system-requirements) for installation instructions. 

| Tool                          | Required | Purpose                                  |
|-------------------------------|----------|------------------------------------------|
| [PHP](#php)                   | Yes      | Required by Composer, Drush, Robo, etc. |
| [Composer](#composer)         | Yes      | Package management.                      |
| [Git](#git)                   | Yes      | Version control.                         |
| [Drush](#drush)               | Yes      | CLI integration with Drupal.             |
| [java](#java)                 | No       | Required by Selenium.                    |
| [chromedriver](#chromedriver) | No       | Required by Selenium.                    |
| [ansible](#ansible)           | No       | Required by Drupal VM.                   |
| [vagrant](#vagrant)           | No       | Required by Drupal VM.                   |
| [virtualbox](#virtualbox)     | No       | Required by Drupal VM.                   |
| [Yarn](#yarn)                 | No       | Required by Cog.                         |
| [NVM](#nvm)                   | No       | Required by Cog.                         |

## Validation & testing tools

These tools are installed automatically by BLT via Composer.

| Tool                       |
|----------------------------|
| [Behat](#behat)            |
| [PHPUnit](#phpunit)        |
| [PHP Code Sniffer](#phpcs) |



## Local environments

You can use _any_ Drupal-compatible local development environment with BLT. However, BLT provides specific support for the following tools. 

| Tool                              | 
|-----------------------------------|
| [Acquia DevDesktop](#dev-desktop) |
| [Drupal VM](#drupal-vm)           |

Please see [local development](local-development.md) for more information.

## CI/CD solutions

You can use _any_ Continuous Integration or Continuous Delivery tool with BLT. However, BLT provides specific support (in the form of default configuration files) for the following tools.

| Tool                                  |
|---------------------------------------|
| [Acquia Pipelines](#acquia-pipelines) |
| [Travis CI](#travis-ci)               |

Please see [Continuous integration](ci.md) for more information.

## Hosting

You can host a BLT project in _any_ Drupal-compatible hosting environment. However, BLT provides specific support for Acquia Cloud and Acquia Cloud Site Factory by:

* Providing cloud hooks
* Providing Acquia-specific default configuration in settings.php
* Structuring project directories to match Acquia Cloud repository's default structure

### <a name="php">PHP</a>

[PHP](http://php.net/manual/en/install.php) is required by various tools, including Composer, Drush, Robo, and Drupal itself. Please ensure that:

* You are using PHP 5.6+. You can check your existing version by executing `php -v`.
* You set the memory_limit for PHP to 2G or higher (for Composer). You can find the `php.ini` file for your PHP CLI by executing `php --ini` and looking for the "Loaded Configuration file".

### <a name="composer">Composer</a>

[Composer](https://getcomposer.org/) is used to manage project level dependencies for BLT and for Drupal. It is the defacto package manager for the PHP community, and is used by Drupal Core itself.

See [dependency management](dependency-management.md) to learn how to use Composer in conjunction with BLT.

* Update to the latest version of composer using `composer self-update`

### <a name="git">Git</a>

[Git](https://git-scm.com/) is a distributed version control system. It is the VCS tool for the Drupal community. 

### <a name="drush">Drush</a>

[Drush](http://www.drush.org/en/master/) is a command line shell and Unix scripting interface for Drupal. BLT uses it to communicate with Drupal via the command line.

_Drush is both a system level and a project level dependency_, which is unusual. Because of this, it is possible to have one version of drush on your system and a different version of drush used within your proejct directory. This is useful but frequently causes confusion.
 
Drush uses a special "launcher" script to look for a copy of drush that is specific to your project. BLT ships such project-level drush binary in the `vendor/bin` directory of your project. Your global drush installation defer to the project level binary when executing `drush` from within a BLT project directory.  

### <a name="java">Java</a>

Java is required by Selenium, which is one option for executing Javascript Behat tests. You may choose NOT to use Selenium, in which case Java is not required. See [testing](testing.md) for more information.

### <a name="headlesschrome">Headless Chrome</a>

Headless Chrome is used by default for Behat tests, though you can also use Selenium or PhantomJS.

*Special note for Docker users*

Connections to Headless Chrome will occasionally time out in containerized environments such as Docker. See this issue for discussion and possible solutions: https://github.com/acquia/blt/issues/2083

### <a name="chromedriver">Chromedriver</a>

Chromedriver is required by Selenium to communicate with Chrome. Selenium is one option for executing Javascript Behat tests. You may choose NOT to use Selenium, in which case chromedriver is not required. See [testing](testing.md) for more information.

### <a name="ansible">Ansible</a>

[Ansible](https://www.ansible.com/) is required by [Drupal VM](https://www.drupalvm.com/), which is one option for local development. You may choose NOT to use Drupal VM, in which case Ansible is not required.

### <a name="vagrant">Vagrant</a>

[Vagrant](http://vagrantup.com/) is required by [Drupal VM](https://www.drupalvm.com/), which is one option for local development. You may choose NOT to use Drupal VM, in which case Vagrant is not required.

### <a name="virtualbox">VirtualBox</a>

[VirtualBox](https://www.virtualbox.org/wiki/VirtualBox) is required by [Drupal VM](https://www.drupalvm.com/), which is one option for local development. You may choose NOT to use Drupal VM, in which case VirtualBox is not required.

### <a name="yarn">Yarn</a>

[Yarn](https://github.com/yarnpkg/yarn) is a package manager for Javascript. It is required by [Cog](https://github.com/acquia-pso/cog), which is one option for a Drupal base theme. You may choose NOT to use Cog, in which case Yarn is not required.

### <a name="nvm">NVM</a>

[NVM](README.markdown) manages multiple versions of NodeJS ona single machine. It is required by [Cog](https://github.com/acquia-pso/cog), which is one option for a Drupal base theme. You may choose NOT to use Cog, in which case NVM is not required.
