# ACSF Setup

To configure a project to run on ACSF, perform the following steps after initially setting up BLT, but _before_ creating any sites in ACSF:

1. Execute `blt recipes:acsf:init:all` from the project root and commit any changes.
1. Create one or more custom profiles:
    - If you are using Lightning, create a custom sub-profile as described [here](https://docs.acquia.com/lightning/subprofile).
    - For non-Lightning use cases, a profile can be generated [via Drupal Console](https://hechoendrupal.gitbooks.io/drupal-console/content/en/commands/generate-profile.html).
1. Add `acsf` as a dependency for your profile.
1. Deploy to Cloud using `blt artifact:deploy`. (Code can also be deployed via a [Continuous Integration setup](http://blt.readthedocs.io/en/stable/readme/deploy/#continuous-integration).)
1. Use ACSF's "update code" feature to deploy the artifact.
1. When creating a new site, select your custom profile as the profile.

In all other respects, BLT treats ACSF installations as multisite installations. To finish setup, including to set up a local development environment for your ACSF project, follow the steps in the [multisite readme](multisite.md).

### Troubleshooting

If you receive an error such as `Could not retrieve the site standard_domain from the database.` when updating code on ACSF, it indicates that one or more sites on your subscription does not have the ACSF connector module enabled and configured. Enable this module and then try updating code again.

### Resources

* [Site Factory Documentation](https://docs.acquia.com/site-factory/)
* [Acquia Cloud Site Factory Connector Module](https://www.drupal.org/project/acsf)
