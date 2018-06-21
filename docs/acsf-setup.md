# ACSF Setup

To configure a project to run on ACSF, perform the following steps after initially setting up BLT:

1. Execute `blt recipes:acsf:init:all` from the project root.
1. Create a custom profile:
    - If you are using Lightning, create a custom sub-profile as described [here](https://docs.acquia.com/lightning/subprofile).
    - For non-Lightning use cases, a profile can be generated [via Drupal Console](https://hechoendrupal.gitbooks.io/drupal-console/content/en/commands/generate-profile.html).
1. Add `acsf` as a dependency for your profile.
1. Modify the `profile` key under `project` in `blt/blt.yml` to use the newly created custom profile. See example below with a profile named `mycustomprofile`:

        project:
          machine_name: blted8
          prefix: BLT
          human_name: 'BLTed 8'
          profile:
            name: mycustomprofile

1. Deploy to Cloud using `blt artifact:deploy`. (Code can also be deployed via a [Continuous Integration setup](http://blt.readthedocs.io/en/stable/readme/deploy/#continuous-integration).)
1. Use ACSF's "update code" feature to push the artifact out to sites.
1. When creating a new site, select your custom profile as the profile.

To set up a local development environment for your ACSF project, follow the steps in the [multisite readme](multisite.md).

### Resources

* https://docs.acquia.com/site-factory/quick-start/configure
* https://www.drupal.org/project/acsf
