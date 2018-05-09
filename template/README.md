# My Project

A brief description of My Project.

## Using This Template

Remove this section after initial setup!

Search for and replace the following placeholders:

* \<organization>
* \<project>
* \<project local url>
* \<local environment>
* \<project key>
* \<branch>
* \<provide link>

## Getting Started

This project is based on BLT, an open-source project template and tool that enables building, testing, and deploying Drupal installations following Acquia Professional Services best practices. While this is one of many methodologies, it is our recommended methodology. 

* Review the [Required / Recommended Skills](http://blt.readthedocs.io/en/latest/readme/skills) for working with a BLT project
* Ensure that your computer meets the minimum installation requirements (and then install the required applications). See the [System Requirements](http://blt.readthedocs.io/en/latest/INSTALL/#system-requirements).
* Request access to organization that owns the project repo in GitHub (if needed)
* Fork the project repository in GitHub
* Request access to the Acquia Cloud Environment for your project (if needed)
* Setup a SSH key that can be used for GitHub and the Acquia Cloud (you CAN use the same key)
    * [Setup GitHub SSH Keys](https://help.github.com/articles/adding-a-new-ssh-key-to-your-github-account/)
    * [Setup Acquia Cloud SSH Keys](https://docs.acquia.com/acquia-cloud/ssh/generate)
* Clone your forked repository. By default, Git names this "origin" on your local.
```
$ git clone git@github.com:<account>/<project>.git
```
* To ensure that upstream changes to the parent repository may be tracked, add the upstream locally as well.
```
$ git remote add upstream git@github.com:<organization>/<project>.git
```
* Install Composer Dependencies (warning: this can take some time based on internet speeds)
```
$ composer install
```
* Setup Local Environment 

BLT requires "some sort" of local environment that implements a LAMP stack. While we provide out of the box templates for Drupal VM, if you prefer you can use another tool such as Docker, Docksal, Lando, (other) Vagrant, or your own custom LAMP stack. BLT works with any local environment, however support is limited for these solutions.

For instructions on setting up Drupal VM, (read our documentation here)[http://blt.readthedocs.io/en/9.x/readme/local-development/#using-drupal-vm-for-blt-generated-projects].

* Run the initial Setup
```
$ vagrant ssh
$ blt setup
``` 
* Access the site and do necessary work at \<project local url> by running

```
$ drush uli
```

Additional [BLT documentation](http://blt.readthedocs.io) may be useful. You may also access a list of BLT commands by running:
```
$ blt
``` 

Note the following properties of this project:
* Primary development branch: \<branch>
* Local environment: \<local environment>
* Local site URL: \<project local url>

## Working With a BLT Project

BLT projects are designed to instill software development best practices (including git workflows). 

Our BLT Developer documentation includes an (example workflow)[http://blt.readthedocs.io/en/latest/readme/dev-workflow/#workflow-example-local-development].

### Important Configuration Files

BLT uses a number of configuration (.yml or .json) files to define and customize behaviors. Some examples of these are:

* blt/blt.yml (formerly blt/project.yml prior to BLT 9.x)
* blt/local.blt.yml
* box/config.yml (if using Drupal VM)
* drush/sites (contains Drush aliases for this project)
* composer.json (includes required components, including Drupal Modules, for this project)

## Resources

* JIRA - \<provide link>
* GitHub - \<provide link>
* Acquia Cloud subscription - \<provide link>
* TravisCI - \<provide link>
