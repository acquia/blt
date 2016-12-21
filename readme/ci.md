## Continuous Integration

BLT provides automation commands that can be used in most OSX and Linux environments. These commands are intended to be used both locally and on CI platforms.

Two CI solutions are supported out-of-the-box:

1. [Acquia Pipelines](#acquia-pipelines)
1. [Travis CI](#travis-ci)

BLT provides one default instruction file (e.g., .travis.yml or acquia-pipelines.yml) for each of these CI solutions, allowing you to have a working build out-of-the-box. To use the default instruction file you must run an initialization command (detailed below). This will copy the default instruction file to the required location, much in the way that Drupal requires you to copy default.settings.php to settings.php.
 
The instruction files are intended to be customized. BLT will provide updates to the default instruction files, but it is your responsibility to merge those updates into your customized files.

### Workflow

The typical CI workflow is as follows:

1. A pull request or commit to GitHub triggers a CI build.
1. An instruction file is read and executed by the CI tool. The instruction file executes BLT commands to build and test your application. For example:
    - Composer dependencies are built
    - Code is linted, sniffed, and otherwise validated
    - Drupal is installed
    - Tests (PHPUnit, Behat, etc.) are run against the installed instance of Drupal
1. The CI tool reports the status of the build (success or failure) back to GitHub.
1. If the build was successful, a human merges the pull request.
1. The merge triggers yet another CI build. In addition to building and testing your application again, this build will generate an artifact that is suitable for deployment.
1. The artifact is deployed. If this is done automatically, it is considered Continuous Deployment.

### Acquia Pipelines

[Acquia Pipelines](https://docs.acquia.com/pipelines) is a Continuous Integration and Continuous Deployment solution built on Acquia Cloud infrastructure. For Acquia Cloud users, it provides the benefit of integrating directly with an Acquia Cloud subscription. This allows build artifacts to be easily deployed.

To initialize Pipelines support for your BLT project:

1. [Install the Acquia Pipelines client](https://docs.acquia.com/pipelines/install) on your local machine:

        curl -o pipelines https://cloud.acquia.com/pipeline-client/download
        chmod a+x pipelines

1. [Configure the Pipelines client](https://docs.acquia.com/pipelines/install#authenticate) 
1. Initialize Pipelines for your project

        blt ci:pipelines:init
   
   This will generate an [acquia-pipelines.yml file](https://docs.acquia.com/pipelines/yaml) in your project root based on [BLT's default acquia-pipelines.yml file](https://github.com/acquia/blt/blob/8.x/scripts/pipelines/acquia-pipelines.yml).

1. Commit the new file and push it to your Acquia git remote. Example commands:

        git add acquia-pipelines.yml
        git commit -m 'Initializing Pipelines integration.'
        git push origin

1. Initialize GitHub integration for your project. See `pipelines init-github --help` or review [help documentation](https://docs.acquia.com/pipelines/github) for instructions. E.g.,

        pipelines init-github REPO MY_TOKEN

1. Submit a pull request to your GitHub repository.

It is expected that your new pull request will trigger a Pipelines build to begin. The status should be reported on the pull request's web page. If merged, Pipelines will generate a new branch on your Acquia subscription named "pipelines-[source-branch]-build". The branch will contain a deployment artifact that can be deployed to an Acquia environment.

#### Additional information

You may [use the Pipelines client](https://docs.acquia.com/pipelines/client) to do things like check the status or logs for your build:

    # List all pipelines applications.
    pipelines list-applications
    # Set the default application id for your repo locally, so you don't need to type it for every subsequent command.
    pipelines set-application-id --application-id=[application ID]
    # Show status of all builds.
    pipelines status
    # Show logs for most recent build.
    pipelines log

##### Resources:

* [Acquia Pipelines overview](https://docs.acquia.com/pipelines)
* [Using the Acquia Pipelines client](https://docs.acquia.com/pipelines/client)
* [Acquia Pipelines troubleshooting guide](https://docs.acquia.com/pipelines/troubleshooting)

### Travis CI

[Travis CI](https://travis-ci.org/) is a Continuous Integration and Continuous Deployment solution. It can be made to integrate with Acquia Cloud, but requires a bit more initial setup work than Acquia Pipelines.

#### Setting Up Travis CI for automated deployments

To set up the [workflow described earlier](#workflow), you must configure Acquia Cloud, GitHub, and Travis CI to work together. Step-by-step instructions are provided below. _These instructions apply only to private GitHub repositories._

1. Initialize Travis CI support for your project

         blt ci:travis:init

1. Generate an SSH key locally for Travis / Build use. (e.g.)

         cd ~/.ssh
         ssh-keygen -t rsa -b 4096

   Do not use a passphrase!
   Name this key something different than your normal Acquia Cloud key (e.g. travis)

1. Login to your Acquia Cloud account and add the public SSH key from the key pair that was generated in step 1 by visiting `https://accounts.acquia.com/account/[uid]/security`.
1. Add the same public SSH key to the "Deployment Keys" section on your project's GitHub settings page, located at `https://github.com/acquia-pso/[project-name]/settings/keys`. **Note: You may not have direct access to these settings if you do not have administrative control over your repository.**
1. Add the _private SSH key_ to your project's Travis CI settings located at `https://magnum.travis-ci.com/acquia-pso/[project-name]/settings`.
1. Add your cloud git repository to the remotes section of your project.yml file:

        remotes:
           - example@svn-14671.prod.hosting.acquia.com:example.git`

   Note: if planning on executing any drush sql-syncs/rsyncs between the cloud and your environment, also add the test/stage server host here and ensure that your account has the senior developer or team lead role (or appropriate custom access in the Acquia Cloud).

1. Add your cloud git repository's server host name to `ssh_known_hosts` in your .travis.yml file. Take care to remove the user name and file name (example.git) and use only the hostname.

        addons:
          ssh_known_hosts:
          - svn-14671.prod.hosting.acquia.com
   
   Note: if planning on executing any drush sql-syncs/rsyncs between the cloud and your environment, also add the test/stage server host here and ensure that your account has the senior developer or team lead role (or appropriate custom access in the Acquia Cloud).

1. Commits or merges to the develop branch on GitHub should now trigger a fully built artifact to be deployed to your specified remotes.
1. **If the build account is removed from the project, the Travis Builds for that project will begin to fail.**

For information on manually deploying your project, read [deploy.md](deploy.md)

#### Setting Up Travis CI for automated deployments on multiple branches

You can monitor multiple branches on github for deployment, for example master and integration, by adding another "provider" block to the deploy section of your project's .travis file. You can add as many provider blocks as needed.

````
deploy:
   - provider: script
     script: blt deploy -Ddeploy.commitMsg="Automated commit by Travis CI for Build ${TRAVIS_BUILD_ID}" -Ddeploy.branch="${TRAVIS_BRANCH}-build"
     skip_cleanup: true
     on:
       branch: master

   - provider: script
     script: blt deploy -Ddeploy.commitMsg="Automated commit by Travis CI for Build ${TRAVIS_BUILD_ID}" -Ddeploy.branch="${TRAVIS_BRANCH}-build"
     skip_cleanup: true
     on:
       branch: integration
````


#### Automated testing using live content

By default, the Travis CI automated tests install and test your site from scratch. You may also run automated tests against a copy of your production database. This allows you to functionally test update hooks.

Automated testing of live content is easy to set up with two simple steps:

1. Add the hostname of your staging server to .travis.yml:

         ssh_known_hosts:
           - staging-12345.prod.hosting.acquia.com

2. Follow the steps in [extending BLT](extending-blt.md) to override the default `ci:build:validate:test` target:

         <!-- Override the core ci:build:validate:test target to include a local refresh-->
         <target name="ci:build:validate:test" description="Builds, validates, tests, and deploys an artifact."
           depends="validate:all, ci:setup, local:sync, local:update, tests:all" />
