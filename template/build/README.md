# Build files

This directory contains configuration files for running common project tasks. These may be used for running tasks locally, or for running automated builds via continuous integration solutions.

This directory should not contain any test files. Those exist in the [/tests](/tests) directory.

## Build Tasks

A large number of common build tasks are provided via Phing targets. These include tasks for things like code sniffing, executing tests, building dependencies, installing Drupal, etc.

For a full list of available Phing tasks, run `./bolt.sh -list` from the project's root directory.

### Executing Tasks

* For a full list of the available Phing targets, run `./bolt.sh -list`
* To manually test a Phing target, run the following command matching the the following pattern: `./bolt.sh <target-name>`. For example `./bolt.sh validate:all`
* To run Phing directly from the binary, simply run `./bin/phing -f build/phing/build.xml <arguments>`

## <a name="ci"></a> Continuous Integration

Integration with Travis CI is included, although Phing tasks can be used with any CI tool. The default Travis CI build process is as follows:

1. Pull request or commit to GitHub triggers Travis CI.
1. `.travis.yml` is read and executed by Travis CI. The environment is built by installing composer dependencies.
1. Travis CI begins a a build and calls various Phing targets.

### Creating your own custom tasks

You may add your own custom tasks to the build engine by defining new [Phing](https://www.phing.info/) targets in [build/custom/phing/build.xml]
(custom/phing/build.xml).

You may override or define custom Phing properties in [build/custom/phing/build.yml](custom/phing/build.yml)

### Setting Up Travis CI for automated deployments

Travis CI can be used to deploy a fully built site artifact (with the docroot) in the following manner:

1. A pull request is merged into the GitHub repository
2. Travis builds the docroot
3. Travis commits the docroot to a specific "build" branch and pushes to Acquia Cloud
   
To set up this workflow, you must configure Acquia Cloud, GitHub, and Travis CI to work together. Step-by-step instructions are provided below.


1. Generate an SSH key locally. E.g.,
   ````
   cd ~/.ssh
   ssh-keygen -t rsa
   ````
   Do not use a passphrase!
1. Create a new Acquia Cloud account to be used exclusively as a container for the SSH keys that will grant Travis push access to Acquia Cloud. This can be done by inviting a new team member on the "Teams" tab in Acquia Cloud. You can use an email address like `<email>+travis@acquia.com`. The team member must have SSH push access.
1. Login the your new Acquia Cloud account and add the public SSH key from the key pair that was generated in step 1 by visiting `https://accounts.acquia.com/account/[uid]/security`.
1. Add the same public SSH key to the "Deployment Keys" section on your project's GitHub settings page, located at `https://github.com/acquia-pso/[project-name]/settings/keys`.
1. Add the _private SSH key_ to your project's Travis CI settings located at `https://magnum.travis-ci.com/acquia-pso/[project-name]/settings`.
1. Update your .travis.yml file to include the following lines:
  ````
   after_success:
     - scripts/deploy/travis-deploy.sh build:validate:test develop develop-build
  ````
  Note that two branch names are referenced in this example: "develop" and "develop-build". This example will watch for changes to the "develop" branch on GitHub and deploy a build artifact to the "develop-build" branch on Acquia Cloud when the 'build:validate:test' Travis job is successful.
1. Add your cloud git repository to the remotes section of your project.yml file:
  ````
  remotes:
     - example@svn-14671.prod.hosting.acquia.com:example.git`
  ````
1. Add your cloud git repository's server host name to `ssh_known_hosts` in you .travis.yml file.
  ````
  addons:
    ssh_known_hosts:
    - svn-14671.prod.hosting.acquia.com
  ````
1. Commits or merges to the develop branch on GitHub should now trigger a fully built artifact to be deployed to your specified remotes.

For information on manually deploying your project, read [readme/deploy.md](readme/deploy.md)
