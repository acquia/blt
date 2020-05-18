.. include:: ../../common/global.rst

Local development with Acquia BLT
=================================

Acquia does not recommend or support any particular solution for local
development of Drupal sites. However, several community-supported solutions
exist:

-  `Drupal VM
   <https://support.acquia.com/hc/en-us/articles/360047166053-Using-Drupal-VM-for-Acquia-BLT-generated-projects>`__:
   An isolated virtual machine (VM), built with Vagrant and Ansible.
-  `Acquia Dev Desktop
   <https://support.acquia.com/hc/en-us/articles/360046519034-Using-Acquia-Dev-Desktop-for-BLT-generated-projects>`__:
   A turn-key LAMP stack tailored specifically for Acquia-hosted Drupal websites.
-  `Lando <https://support.acquia.com/hc/en-us/articles/360047166273-Configuring-Acquia-BLT-with-Lando>`__:
   A container-based Drupal development solution.

Regardless of the local environment you select, use the following guidelines:

-  To guarantee similar behavior, use Apache as your web server.
-  If you host your project on Acquia Cloud, be sure to match
   :doc:`our software versions </acquia-cloud/arch/tech-platform/>`.

Acquia developers use `PHPStorm <http://www.jetbrains.com/phpstorm/>`__ and
recommend it for local development environments. Acquia has written several
`Knowledge Base articles <https://support.acquia.com/>`__ about using
PHPStorm for Drupal development.

.. Next review date 20200422
