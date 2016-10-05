## Continuous Integration

Integration with Travis CI is included, although Phing tasks can be used with any CI tool. The default Travis CI build process is as follows:

1. Pull request or commit to GitHub triggers Travis CI.
1. `.travis.yml` is read and executed by Travis CI. The environment is built by installing composer dependencies.
1. Travis CI begins a a build and calls various Phing targets.

### Automated testing using live content

By default, the Travis CI automated tests install and test your site from scratch. Once you have a production site in a remote environment, it’s recommended to also run automated tests against a copy of your production database, especially in order to functionally test update hooks.

Automated testing of live content is easy to set up with two simple steps:

1. Add the hostname of your staging server to .travis.yml:

         ssh_known_hosts:
           - staging-12345.prod.hosting.acquia.com

2. Follow the steps in [extending BLT](extending-blt.md) to override the default `ci:build:validate:test` target:

         <!-- Override the core ci:build:validate:test target to include a local refresh-->
         <target name="ci:build:validate:test" description="Builds, validates, tests, and deploys an artifact."
           depends="validate:all, ci:setup, local:sync, local:update, tests:all" />


### Setting Up Travis CI for automated deployments

Travis CI can be used to deploy a fully built site artifact (with the docroot) in the following manner:

1. A pull request is merged into the GitHub repository
2. Travis builds the docroot
3. Travis commits the docroot to a specific "build" branch and pushes to Acquia Cloud

To set up this workflow, you must configure Acquia Cloud, GitHub, and Travis CI to work together. Step-by-step instructions are provided below. _The following instructions apply only to private GitHub repositories._

1. Initialize Travis CI support for your project

         blt ci:travis:init

1. Generate an SSH key locally. E.g.,

         cd ~/.ssh
         ssh-keygen -t rsa -b 4096

   Do not use a passphrase!

1. Create a new Acquia Cloud account to be used exclusively as a container for the SSH keys that will grant Travis push access to Acquia Cloud. This can be done by inviting a new team member on the "Teams" tab in Acquia Cloud. You can use an email address like `<email>+travis@acquia.com`. The team member must have SSH push access.
1. Login to the new Acquia Cloud account and add the public SSH key from the key pair that was generated in step 1 by visiting `https://accounts.acquia.com/account/[uid]/security`.
1. Add the same public SSH key to the "Deployment Keys" section on your project's GitHub settings page, located at `https://github.com/acquia-pso/[project-name]/settings/keys`. **Note: You may not have direct access to these settings if you do not have administrative control over your repository.**
1. Add the _private SSH key_ to your project's Travis CI settings located at `https://magnum.travis-ci.com/acquia-pso/[project-name]/settings`.
1. Add your cloud git repository to the remotes section of your project.yml file:

        remotes:
           - example@svn-14671.prod.hosting.acquia.com:example.git`

1. Add your cloud git repository's server host name to `ssh_known_hosts` in your .travis.yml file. Take care to remove the user name and file name (example.git) and use only the hostname.

        addons:
          ssh_known_hosts:
          - svn-14671.prod.hosting.acquia.com

1. Commits or merges to the develop branch on GitHub should now trigger a fully built artifact to be deployed to your specified remotes.

For information on manually deploying your project, read [deploy.md](deploy.md)

### Setting Up Travis CI for automated deployments on multiple branches
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
