.. include:: ../../common/global.rst

Open Source Contributions
=========================

.. _blt-what-not-to-release:

What not to release
-------------------

-  Use-case specific features (such as information no one else cares about)

   -  When in doubt, release.

-  Confidential or security sensitive information

   -  Definitions will depend upon applicable NDA or security agreements.


.. _blt-what-to-release:

What to release
---------------

-  Patches to core or contrib

   -  Should always be contributed, if appropriate for public release.
   -  Make use of organization crediting.

-  Modules

   -  Ensure that these are properly generalized and abstracted.
   -  Ensure that client information is removed from code.


.. _blt-os-contrib-recs:

Recommendation
--------------

An individual user (such as an architect or developer) releases to
Drupal.org.

-  Team should select who should own the module based on contribution to
   its creation and ability or interest in on-going ownership.
-  Ensure that maintainer transitioning is included in the process for
   off-boarding of staff.
-  Make use of organization crediting.
-  Benefits

   -  Greater community visibility.
   -  Can make use of Drupal.org packaging and testing bots.

-  Drawbacks

   -  Organization does not have ownership of the module.

Alternatives
~~~~~~~~~~~~

**Organization ownership on Drupal.org**

Create a Drupal.org account to represent the organization. Use this
account to maintain the project.

-  Benefits

   -  Organization cannot lose control of the module.
   -  Can serve NDA processes.

-  Drawbacks

   -  Organization users are not supported on Drupal.org; requires
      help from Drupal Association staff.

**Releasing on GitHub with Drupal.org project page**

-  Benefits

   -  Organization ownership is guaranteed.
   -  GitHub project flows (such as pull requests).

-  Drawbacks

   -  Frowned upon by Drupal.org and part of community.

-  Considerations

   -  Must issues be handled on Drupal.org, GitHub, or both?

      -  Recommendation: Issues must be tracked on GitHub to integrate
         best with the workflow.


.. Next review date 20200423
