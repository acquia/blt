# Patches
 
All modifications to contributed projects must be performed via a patch and must be applied via composer.json.

Drupal core and contrib can be patched in `composer.json` using `cweagans/composer-patches`, which is required by default. Patch information should be specified in the JSON array in accordance with the following schema:

    "extra": {
      "patches": {
        "drupal/core": {
          "Ignore front end vendor folders to improve directory search performance": "https://www.drupal.org/files/issues/ignore_front_end_vendor-2329453-116.patch"
        }
      }
    },

Patches that can be contributed on Drupal.org should be contributed there. Please follow [Drupal.org's patch naming conventions](https://www.drupal.org/node/1054616#naming-conventions) when creating patches.

Patches that cannot be contributed publicly are extremely rare. In the unlikely event that such a change must be committed, all project-specific patches should reside in this directory. This ensures one consistent place for patches and avoids accidental patch deletion.

Patches should be stored in sub-directories based on project name being patched.

Examples:

- /patches/drupal/some_patch-1234-1.patch
- /patches/ctools/another_patch_name-9876-12.patch
