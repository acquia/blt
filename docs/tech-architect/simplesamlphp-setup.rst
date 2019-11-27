.. include:: ../../common/global.rst

Setting up SSO with SimpleSAMLphp
=================================

Use the information in this documentation page to set up single sign-on (SSO)
with SimpleSAMLphp on a working Acquia BLT website.


.. _blt-sso-prep:

Preparation
-----------

Request the remote IdP metadata (XML) from the subscriber. Each
environment (for example, dev, stage, and prod) will have its own metadata.

You can start to update your code for SSO before you receive these files, but
will have to pause when you reach the steps that involve needed information
(such as configuring your IdP Remote metadata).


.. _blt-sso-code-changes:

Updating your code for SSO
--------------------------

#.  Run the following command to perform initial installation tasks:

    .. code-block:: text

       blt recipes:simplesamlphp:init

    Tasks completed by this command include the following:

    -  Adds the `simpleSAMLphp
       Authentication <https://www.drupal.org/project/simplesamlphp_auth>`__
       module as a project dependency in your ``composer.json`` file.
    -  Copies configuration files to
       ``${project.root}/simplesamlphp/config``.
    -  Adds a ``simplesamlphp`` property to the ``blt/blt.yml`` file.
    -  Creates a symbolic link in the docroot to the web-accessible
       directory of the ``simplesamlphp`` library.

#.  Add the following lines to your ``docroot/.htaccess`` file:

    .. code-block:: text

       # Allow access to simplesaml paths.
       RewriteCond %{REQUEST_URI} !^/simplesaml

    For example, as depicted in the following *diff* results:

    .. code-block:: text

         # Copy and adapt this rule to directly execute PHP files in
         # contributed or custom modules or to run another PHP application in
         # the same directory.
         RewriteCond %{REQUEST_URI} !/core/modules/statistics/statistics.php$
       + # Allow access to simplesaml paths.
       + RewriteCond %{REQUEST_URI} !^/simplesaml
         # Deny access to any other PHP files that do not match the rules
         # above.
         RewriteRule "^.+/.*\.php$" - [F]

#.  Use the following steps to update your
    ``${project.root}/simplesamlphp/config/acquia_config.php`` file:

    a.  Update the following values in the ``$config`` array:

        .. code-block:: text

          // The technical contact for the SAML identity provider, i.e., the customer.
          $config['technicalcontact_name'] = "Joe IT";
          $config['technicalcontact_email'] = "joe.it@example.com";
          $config['secretsalt'] = 'y0h9d13pki9qdhfm3l5nws4jjn55j6hj';
          $config['auth.adminpassword'] = 'mysupersecret';

    #.  *(Optional)* Set the following values to password protect the
        SimpleSAMLphp pages (the password will be the value of
        ``$config['auth.adminpassword']``):

        .. code-block:: text

          $config['admin.protectindexpage'] = TRUE;
          $config['admin.protectmetadata'] = TRUE;

#.  Configure IdP remote metadata by completing the following procedure:

    a.  Run the following command to copy the configuration files to the
        local SimpleSAML library:

        .. code-block:: bash

            blt source:build:simplesamlphp-config

        .. note::

            This command is strictly for local use, and because the command
            overwrites vendor files, running the command will make not make
            any changes that are visible to Git. Acquia BLT's build process
            will handle this for the deployable build artifact.

    #.  Sign in to the SimpleSAMLphp installation page on your local website
        at ``/simplesaml/``, using the password you defined in
        ``$config['auth.adminpassword']``.

    #.  Navigate to the *XML to SimpleSAMLphp metadata converter*
        (``/simplesaml/admin/metadata-converter.php``), which is located
        on the **Federation** tab under **Tools**.

    #.  *(Optional)* Remove the default metadata from
        ``${project.root}/simplesamlphp/metadata/saml20-idp-remote.php``.

    #.  For each metadata (XML) file from the subscriber, parse the file using
        this tool, and then copy the converted ``saml20-idp-remote`` metadata
        into the
        ``${project.root}/simplesamlphp/metadata/saml20-idp-remote.php`` file.

#.  Use the following steps to configure the ``authsources.php`` file:

    a.  Edit the ``${project.root}/simplesamlphp/config/authsources.php``
        file using `SimpleSAMLphp Service Provider
        QuickStart <https://simplesamlphp.org/docs/stable/simplesamlphp-sp>`__
        as a guide (except enabling a certificate for your service
        provider, which should be done according to the following
        instructions). Note the ``name`` option, by which you can give each
        IdP a human-readable name (for example, "Dev" or "Prod") for use in
        the administrative user interface.

    #.  If your Identity Provider or Federation requires that your service
        providers hold a certificate, complete the following steps:

        i.  Create a self-signed certificate in the
            ``${project.root}/simplesamlphp/cert`` directory:

            .. code-block:: text

               cd simplesamlphp/cert
               openssl req -x509 -sha256 -nodes -days 3652 -newkey rsa:2048 -keyout saml.pem -out saml.crt

        #.  Edit your
             ``${project.root}/simplesamlphp/config/authsources.php`` entry,
             and add references to your certificate:

             .. code-block:: text

                'default-sp' => array(
                  'saml:SP',
                  'privatekey' => 'saml.pem',
                  'certificate' => 'saml.crt',
                  'signature.algorithm' => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',
                ),

        .. note::

           Repeat this process for each of your environments, assuming you use
           different service providers for your dev, stg, and prod
           environments.

#.  Commit your changes to your Git repository.


.. _blt-sso-testing-library-configuration:

Testing library configuration
-----------------------------

After you have completed the preceding code changes, you should proceed with
testing the configuration of SimpleSAMLphp as a Service Provider connecting to
your specified Identity Provider.

You can do this by navigating to the path
``/simplesaml/module.php/core/authenticate.php`` in your application to access
the SimpleSAMLphp library Test authentication sources page. On this page you
will need to click the authentication source to test. It should be
**default-sp** unless configured otherwise.

If it successfully connects and authenticates, it will return you to a page in
the SimpleSAML interface where it will list the attributes returned by the
IdP. Be sure to note these attributes, as you will use them to configure the
`simpleSAMLphp Authentication
<https://www.drupal.org/project/simplesamlphp_auth>`__ Drupal module.

If you are unable to connect, there's typically variations in how your
``authsources.php`` file can be configured, depending on the IdP that's being
used. Due to this, the testing phase in the library is crucial in
determining what changes (if any) need to be made.


.. _blt-sso-module-configuration:

Module configuration
--------------------

Use the following procedure to enable and configure SSO with your Drupal
website:

.. important::

   Be aware that a configuration error during this process can effectively
   block access to your website. We recommend allowing at least user1 to
   continue to use *local authentication* until you confirm your SSO is
   working properly.

#.  :ref:`Enable <resource-d8-enable-module>` the `simpleSAMLphp
    Authentication <https://www.drupal.org/project/simplesamlphp_auth>`__
    module.

#.  Activate authentication using SimpleSAMLphp, and then configure the
    module (based on your requirements) at
    ``/admin/config/people/simplesamlphp_auth``.

#.  Capture the configuration changes with your configuration management
    method of choice.

    If you have multiple service providers for different environments, we
    recommend you use a config split workflow to allow your configuration to
    properly track which service provider should be used for each
    environment.


.. _blt-sso-integration:

Integration
-----------

Repeat the following steps for each environment that requires SAML
authentication:

#.  Deploy the code and configuration captured in previous steps (including
    enabling the `simpleSAMLphp Authentication
    <https://www.drupal.org/project/simplesamlphp_auth>`__ module).

#.  Sign in to the SimpleSAMLphp installation page at ``/simplesaml/``
    using the password you defined in ``$config['auth.adminpassword']``.

#.  Navigate to the **Federation** tab.

#.  Send the subscriber the metadata (XML) for each of your service providers
    (SPs) listed on the webpage.


.. _blt-sso-debugging:

Debugging
---------

The following resources can be useful when debugging SSO-related issues:

-  `SAML Chrome Panel
   <https://chrome.google.com/webstore/detail/saml-chrome-panel/paijfdbeoenhembfhkhllainmocckace>`__:
   Extends the Chrome Developer Tools, and enables the display of SAML
   requests and responses in the Developer Tools window.

-  `SAML tracer
   <https://addons.mozilla.org/en-US/firefox/addon/saml-tracer/>`__:
   Firefox-based tool for viewing SAML messages sent through the browser,
   during single sign-on and single sign-out.

.. Next review date 20200422
