.. include:: ../../common/global.rst

Patches
=======

All modifications to contributed projects and most modifications to Drupal
core must be performed using *patches*.


.. _blt-applying-patches:

Applying patches
----------------

Patches can be applied by referencing them in the ``composer.json`` file, in
the following format. Acquia BLT then uses `cweagans/composer-patches
<https://github.com/cweagans/composer-patches>`__ to apply the patches on any
subsequent website builds.

Patch information should be specified in the JSON array in accordance with
the following schema:

.. code-block:: text

      "extra": {
        "patches": {
          "drupal/core": {
            "Ignore front end vendor folders to improve directory search performance": "https://www.drupal.org/files/issues/ignore_front_end_vendor-2329453-116.patch",
            "My custom local patch": "./patches/drupal/some_patch-1234-1.patch"
          }
        }
      },

.. note::

   When a package is patched, it's advisable to pin it to a specific version
   to avoid downloading an updated version that could introduce a patch
   conflict.

After modifying the ``composer.json`` file, run
``composer update VENDOR_NAME/PACKAGE_NAME``, replacing
``VENDOR_NAME/PACKAGE_NAME`` with the name of the patched dependency. For
example:

.. code-block:: bash

      composer update drupal/core

This will apply the patch and update ``composer.lock``. Commit the modified
``composer.json`` and ``composer.lock`` files.

You can also apply the patch by running ``composer update``. This, however,
will update all the project's dependencies, which may not be desired.


.. _blt-storing-patches:

Storing patches
---------------

Patches that can be contributed on Drupal.org should be contributed there. Be
sure to follow `Drupal.org's patch naming conventions
<https://www.drupal.org/node/1054616#naming-conventions>`__ when creating
patches.

Patches that cannot be contributed publicly are extremely rare. In the
unlikely event that such a change must be committed, all project-specific
patches should reside in this directory. This ensures one consistent place for
patches and avoids accidental patch deletion.

Patches should be stored in sub-directories based on the project name being
patched.

Examples
~~~~~~~~

-  ``/patches/drupal/some_patch-1234-1.patch``
-  ``/patches/ctools/another_patch_name-9876-12.patch``


.. _blt-patches-gotchas:

Gotchas
-------

Composer can only patch files that are distributed with Composer packages.
This means that certain files (such as the Drupal core ``.htaccess`` and
``robots.txt``) cannot be easily patched via Composer. These files are not
included in the Drupal core Composer package. Notably, Drupal Scaffold
individually creates these files on updates.

To modify ``.htaccess`` and other unpatchable root files, modify the file in
place, commit it to Git, and then make the following change in the
``composer.json`` file:

.. code-block:: text

      "extra": {
        "drupal-scaffold": {
          "excludes": [
            ".htaccess"
          ]
        }
      },

The downside is that you will need to apply Drupal core updates to these
excluded files on your own.

Alternately, you could leverage the ``post-drupal-scaffold-cmd`` script hook
to apply patches after Drupal Scaffold is finished. See this
`composer-patches issue
<https://github.com/acquia/blt/issues/1135#issuecomment-285404408>`__
for more details.

Also note that there is an issue in the Drupal packaging system that makes it
difficult to patch module and theme ``.info.yml`` files. If you have trouble
applying a patch that modifies an info file, see `this issue
<https://www.drupal.org/node/2858245>`__ for a description and workaround.

.. Next review date 20200422
