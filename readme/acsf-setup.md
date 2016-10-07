# ACSF Setup

To configure a project to run on ACSF, perform the following steps after initially setting up BLT:

1. Execute `blt acsf:init` from the project root.
1. Create a custom module in `docroot/modules/custom` that will be used in lieu of a custom profile.
1. Add `acsf` as a dependency for your custom module.
1. If using lightning, create a custom [lightning.extend.yml](https://github.com/acquia/lightning/blob/8.x-1.x/lightning.extend.yml) and add it to `sites/default`
1. Deploy to Cloud using `blt deploy`
1. Use ACSF's "update code" feature to push the artifact out to sites.
1. When creating a new site, select "Lightning" as the profile.
