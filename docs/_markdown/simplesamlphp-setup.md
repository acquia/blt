# Setting up SSO with SimpleSAMLphp

Follow this guide to set up SSO with SimpleSAMLphp on a working BLT site.

Note that BLT provides commands for automating the setup process for SimpleSAMLphp as described [elsewhere in the Acquia documentation](https://docs.acquia.com/resource/using-simplesamlphp-acquia-cloud-site), and assists in deploying these changes to Acquia Cloud. However, the BLT team does not provide support for more general issues related to SimpleSAMLphp setup, configuration, or implementation. Support requests related to SimpleSAMLphp should be directed to Acquia Support or your Technical Account Manager. 

1. Execute `blt recipes:simplesamlphp:init`. This performs the following initial setup tasks:

      * Adds the [simpleSAMLphp Authentication](https://www.drupal.org/project/simplesamlphp_auth) `simplesamlphp_auth` module as a project dependency in your `composer.json` file
      * Copies configuration files to `${project.root}/simplesamlphp/config`
      * Adds a `simplesamlphp` property to `blt/blt.yml`
      * Creates a symbolic link in the docroot to the web accessible directory of the `simplesamlphp` library

1. Configuring SimpleSAMLphp.

      1. Follow [Acquia's SimpleSAMLphp documentation](https://docs.acquia.com/resource/using-simplesamlphp-acquia-cloud-site/) to configure SimpleSAMLphp locally via the configuration files in `${project.root}/simplesamlphp/config`.

      1. Execute `blt source:build:simplesamlphp-config` to copy these configuration files to the SimpleSAMLphp library locally. (This is strictly for local use. It will make no change visible to Git, because it overwrites vendor files. BLT's build process will handle this for the deployable build artifact.)
