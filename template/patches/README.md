# Patches

All modifications to contributed projects and most modifications to Drupal core must be performed via patches.

## Applying patches

Patches can be applied by referencing them in `composer.json` in the format below. BLT then uses [cweagans/composer-patches](https://github.com/cweagans/composer-patches) to apply the patches on any subsequent site builds.

Patch information should be specified in the JSON array in accordance with the following schema:

    "extra": {
      "patches": {
        "drupal/core": {
          "Ignore front end vendor folders to improve directory search performance": "https://www.drupal.org/files/issues/ignore_front_end_vendor-2329453-116.patch",
          "My custom local patch": "./patches/drupal/some_patch-1234-1.patch"
        }
      }
    },

Note that when a package is patched, it's advisable to pin it to a specific version to avoid downloading an updated version that could introduce a patch conflict.

After modifying `composer.json`, run `composer update VENDOR_NAME/PACKAGE_NAME`, replacing `VENDOR_NAME/PACKAGE_NAME` with the name of the patched dependency, e.g.,

    composer update drupal/core

This will apply the patch and update `composer.lock`. Commit the modified `composer.json` and `composer.lock` files.

_Alternatively the patch can be applied by running `composer update`. This, however, will update all of the project's dependencies, which may not be desired._

## Storing patches

Patches that can be contributed on Drupal.org should be contributed there. Please follow [Drupal.org's patch naming conventions](https://www.drupal.org/node/1054616#naming-conventions) when creating patches.

Patches that cannot be contributed publicly are extremely rare. In the unlikely event that such a change must be committed, all project-specific patches should reside in this directory. This ensures one consistent place for patches and avoids accidental patch deletion.

Patches should be stored in sub-directories based on project name being patched.

Examples:

- /patches/drupal/some_patch-1234-1.patch
- /patches/ctools/another_patch_name-9876-12.patch

## Gotchas

Note that Composer can only patch files that are distributed with Composer packages. This means that certain files (such as the Drupal core `.htaccess` and `robots.txt`) cannot be easily patched via Composer. These files are not included in the Drupal core Composer package (in fact Drupal Scaffold individually creates these files on updates).

In order to modify `.htaccess` and other unpatchable root files, simply modify the file in place, commit it to Git, and make the following change in `composer.json`:

    "extra": {
      "drupal-scaffold": {
        "excludes": [
          ".htaccess"
        ]
      }
    },

The downside here is that you will need to apply drupal core udpates to these excluded files on your own.

Alternatively, you could leverage the `post-drupal-scaffold-cmd` script hook to apply patches after Drupal Scaffold is finished. See [this cweagens/composer-patches issue](https://github.com/acquia/blt/issues/1135#issuecomment-285404408) for more details.



Also note that there’s currently a quirk in the Drupal packaging system that makes it difficult to patch module and theme `.info.yml` files. If you have trouble applying a patch that modifies an info file, see this issue for a description and workaround: https://www.drupal.org/node/2858245
