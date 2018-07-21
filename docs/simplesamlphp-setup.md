# Setting up SSO with SimpleSAMLphp

Follow this guide to set up SSO with SimpleSAMLphp on a working BLT site.

## Preparation

Request the remote IdP metadata (XML) from the customer. Note that each environment (e.g., dev, stage, prod) will have its own metadata. You may proceed with the below code changes while you wait for these files until you reach the steps that involve them (i.e., Configure IdP Remote Metadata).

## Code Changes

1. Execute `blt recipes:simplesamlphp:init`. This performs the following initial setup tasks:

      * Adds the [simpleSAMLphp Authentication](https://www.drupal.org/project/simplesamlphp_auth) `simplesamlphp_auth` module as a project dependency in your `composer.json` file
      * Copies configuration files to `${project.root}/simplesamlphp/config`
      * Adds a `simplesamlphp` property to `blt/blt.yml`
      * Creates a symbolic link in the docroot to the web accessible directory of the `simplesamlphp` library

1. Add the following two lines to `docroot/.htaccess`:

        # Allow access to simplesaml paths.
        RewriteCond %{REQUEST_URI} !^/simplesaml

      For example, as depicted in the "diff" below:

          # Copy and adapt this rule to directly execute PHP files in contributed or
          # custom modules or to run another PHP application in the same directory.
          RewriteCond %{REQUEST_URI} !/core/modules/statistics/statistics.php$
        + # Allow access to simplesaml paths.
        + RewriteCond %{REQUEST_URI} !^/simplesaml
          # Deny access to any other PHP files that do not match the rules above.
          RewriteRule "^.+/.*\.php$" - [F]

1. Edit `${project.root}/simplesamlphp/config/acquia_config.php` as follows:

      1. Update your database name in `$ah_options`:

              $ah_options = array(
                // Use the database "role" without the "stage", e.g., "example", not
                // "exampletest" or "exampleprod".
                'database_name' => 'example',
                'session_store' => array(
                  // Valid values are "memcache" and "database".
                  'prod' => 'database',
                  'test' => 'database',
                  'dev'  => 'database',
                ),
              );

      1. Amend the default values for the simplesaml session store if desired.
         Note, memcache is only supported on PHP < 7 as tagged versions of the
         simplesaml library only support php-memcache rather than the more
         mature (and PHP 7 ready) php-memcached. This has been fixed in https://github.com/simplesamlphp/simplesamlphp/pull/395
         and will likely be included in a future tagged version.

      1. Update the following values in the `$config` array:

              // The technical contact for the SAML identity provider, i.e., the customer.
              $config['technicalcontact_name'] = "Joe IT";
              $config['technicalcontact_email'] = "joe.it@example.com";
              $config['secretsalt'] = 'y0h9d13pki9qdhfm3l5nws4jjn55j6hj';
              $config['auth.adminpassword'] = 'mysupersecret';

      1. Optionally set the following values to password protect the SimpleSAMLphp pages. (The password will be the value of `$config['auth.adminpassword']`.)

              $config['admin.protectindexpage'] = TRUE;
              $config['admin.protectmetadata'] = TRUE;

      1. Optionally set the following values to prevent Varnish from interfering with SimpleSAMLphp.

              // Prevent Varnish from interfering with SimpleSAMLphp.
              // SSL terminated at the ELB/balancer so we correctly set the SERVER_PORT
              // and HTTPS for SimpleSAMLphp baseurl configuration.
              $protocol = 'http://';
              $port = ':80';
              if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
                $_SERVER['SERVER_PORT'] = 443;
                $_SERVER['HTTPS'] = 'true';
                $protocol = 'https://';
                $port = ':' . $_SERVER['SERVER_PORT'];
              }
              $config['baseurlpath'] = $protocol . $_SERVER['HTTP_HOST'] . $port . '/simplesaml/';

1. Configure IdP Remote Metadata.

      1. Execute `blt source:build:simplesamlphp-config` to copy these configuration files to the SimpleSAMLphp library locally. (This is strictly for local use. It will make no change visible to Git, because it overwrites vendor files. BLT's build process will handle this for the deployable build artifact.)

      1. Log into the SimpleSAMLphp installation page on your local site at `/simplesaml/` using the password you defined in `$config['auth.adminpassword']`.

      1. Navigate to the "XML to SimpleSAMLphp metadata converter" (`/simplesaml/admin/metadata-converter.php`), which can be found on the "Federation" tab under "Tools".

      1. Optionally remove the default metadata from `${project.root}/simplesamlphp/metadata/saml20-idp-remote.php`.

      1. For each metadata (XML) file from the customer, parse it using this tool and copy the converted `saml20-idp-remote` metadata into `${project.root}/simplesamlphp/metadata/saml20-idp-remote.php`.

1. Configure authsources.php

      1. Edit `${project.root}/simplesamlphp/config/authsources.php` using [SimpleSAMLphp Service Provider QuickStart](https://simplesamlphp.org/docs/stable/simplesamlphp-sp) as a guide (except enabling a certificate for your service provider, which should be done according to the instructions below). Note especially the `name` option by which you can give each IdP a human-readable name (e.g., "Dev", "Prod") for use in the administrative UI.

      1. If your Identity Provider/Federation requires that your Service Providers hold a certificate...

          1. Create a self-signed certificate in the `${project.root}/simplesamlphp/cert` directory:

                  cd simplesamlphp/cert
                  openssl req -x509 -sha256 -nodes -days 3652 -newkey rsa:2048 -keyout saml.pem -out saml.crt

          2. Edit your `${project.root}/simplesamlphp/config/authsources.php` entry, and add references to your certificate:

                  'default-sp' => array(
                    'saml:SP',
                    'privatekey' => 'saml.pem',
                    'certificate' => 'saml.crt',
                    'signature.algorithm' => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',
                  ),

1. Review `${project.root}/simplesamlphp/config/config.php` and set any values called for by your project requirements.

1. Commit your changes to your Git repository.

## Testing Library Configuration

Once you have completed the code changes above, you should proceed with testing the configuration of SimpleSAMLphp as a Service Provider connecting to your specified Identity Provider.

You can do this by navigating to the path `/simplesaml/module.php/core/authenticate.php` on your application to access the SimpleSAMLphp library Test authentication sources page. On this page you will need to click the authsource to test. It should be default-sp unless configured otherwise.

If it successfully connects and authenticates it will return you to a page in the SimpleSAML interface where it will list the attributes returned by the IdP. Make a note of these attributes because they will be used to configure the SimpleSAMLphp_auth Drupal module.

If you are unable to connect, there’s typically variations in how your authsources.php can be setup depending on the IdP that’s being used, so the testing phase in the library is crucial in determining what changes if any need to be made.

## Module Configuration

Be careful with the following steps as misconfiguration could effectively lock you out of your site. It is probably best to allow at least user 1 to continue to use "local authentication" until you have confirmed that everything works.

1. Enable the simpleSAMLphp Authentication `simplesamlphp_auth` module.

1. Activate authentication via SimpleSAMLphp and configure the module according to your requirements at `/admin/config/people/simplesamlphp_auth`.

1. Capture the configuration changes with your configuration management method of choice.

## Integration

Repeat the following steps for each environment that requires SAML authentication.

1. Deploy the code and configuration captured in previous steps (including enabling the simpleSAMLphp Authentication `simplesamlphp_auth` module).

1. Log into the SimpleSAMLphp installation page at `/simplesaml/` using the password you defined in `$config['auth.adminpassword']`.

1. Navigate to the "Federation" tab.

1. Send the customer the metadata (XML) for each of your service providers (SPs) listed here.

1. @todo Add remaining integration steps.

## Debugging

* [SAML Chrome Panel](https://chrome.google.com/webstore/detail/saml-chrome-panel/paijfdbeoenhembfhkhllainmocckace) extends the Chrome Developer Tools, adding support for SAML Requests and Responses to be displayed in the Developer Tools window.

* [SAML tracer](https://addons.mozilla.org/en-US/firefox/addon/saml-tracer/) for Firefox is a tool for viewing SAML messages sent through the browser during single sign-on and single logout.

