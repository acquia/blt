# Committing Dependencies

Composer's official stance is that you should not commit dependencies.

However, there are sometimes extenuating circumstances that require you to commit your dependencies. In this case, you can commit your dependencies by following these steps:

* Modify your project's `.gitignore` by removing the following lines:

        docroot/core
        docroot/modules/contrib
        docroot/themes/contrib
        docroot/profiles/contrib
        docroot/libraries
        drush/contrib
        vendor

* Create a custom deploy.exclude_file and reference its location in your blt.yml

        mkdir blt/deploy && cp vendor/acquia/blt/scripts/blt/deploy/deploy-exclude.txt blt/deploy/deploy-exclude.txt
        
        deploy:
          exclude_file: ${repo.root}/blt/deploy/deploy_exclude.txt

* Modify your custom deploy_exclude.txt file by removing the following lines:

        /docroot/core
        /docroot/libraries/contrib
        /docroot/modules/contrib
        /docroot/sites/*/files
        /docroot/sites/*/private
        /docroot/themes/contrib
        /drush/contrib
        /vendor

* Set `deploy.build-dependencies` to `false` in your `blt/blt.yml` file:

        deploy:
          build-dependencies: false

* Commit your changes and dependencies:

        git add -A
        git commit -m 'Committing dependencies.'

Your dependencies will now be committed to your repository and copied to your deployment artifact.
