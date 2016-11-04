# SimpleSAMLphp using BLT

To configure SimpleSAMLphp with BLT perform the following steps after initially setting up BLT:

#### <i class="icon-code"></i>  BLT Basic Setup
Execute `blt simplesamlphp:init`. This performs the following initial setup tasks:

  * Adds the `simplesamlphp_auth` module as a project dependency in your `composer.json` file.
  * Copies configuration files to `${project.root}/simplesamlphp`.
  * Adds a `simplesamlphp` property to `project.yml`.
  * Creates a symbolic link in the docroot to the web accessible directory of the `simplesamlphp` library.

> **Note:**

> - The `simplesamlphp_auth` module contains a `composer.json` file where you can find the version (`~1.14.4`) of the SimpleSamlPHP library that will be installed.
> - As part of `blt simplesamlphp:init` BLT creates a `config` directory that contains three important files: `config.php`, `acquia_config.php` and `authsources.php`.

#### <i class="icon-pencil"></i> Basic Config

 - Add the following two lines to `docroot/.htaccess`:

  ```
  # Allow access to simplesaml paths.
  RewriteCond %{REQUEST_URI} !^/simplesaml
  ```

> **Note:**

> For example, as depicted in the "diff" below: 
> ```
    # Copy and adapt this rule to directly execute PHP files in contributed or
    # custom modules or to run another PHP application in the same directory.
    RewriteCond %{REQUEST_URI} !/core/modules/statistics/statistics.php$
  + # Allow access to simplesaml paths.
  + RewriteCond %{REQUEST_URI} !^/simplesaml
    # Deny access to any other PHP files that do not match the rules above.
    RewriteRule "^.+/.*\.php$" - [F]
  ```

- Edit `${project.root}/simplesamlphp/config/acquia_config.php` as follows:

  * Update your database name in `$ah_options`:

    ```
    $ah_options = array(
      // Use the database "role" without the "stage", e.g., "example", not
      // "exampletest" or "exampleprod".
      'database_name' => 'example',
      'session_store' => array(
        // Valid values are "memcache" and "database".
        'prod' => 'memcache',
        'test' => 'memcache',
        'dev'  => 'database',
      ),
    );
    ```

  * Update the following values in the `$config` array:

    ```
    // The technical contact for the SAML identity provider, i.e., the customer.
    $config['technicalcontact_name'] = "Joe IT";
    $config['technicalcontact_email'] = "joe.it@example.com";
    $config['secretsalt'] = 'y0h9d13pki9qdhfm3l5nws4jjn55j6hj';
    $config['auth.adminpassword'] = 'mysupersecret';
    ```

  * Optionally set the following values to password protect the SimpleSAMLphp pages. (The password will be the value of `$config['auth.adminpassword']`.)

    ```
    $config['admin.protectindexpage'] = true;
    $config['admin.protectmetadata'] = true;
    ```

> **Note:**

> - The file `acquia_config.php` is created in the first step i.e. Basic Setup and the file `config.php` must contain a line `include 'acquia_config.php'` that includes that particular file.

- Edit `${project.root}/simplesamlphp/config/authsources.php` as described in [SimpleSAMLphp Service Provider QuickStart](https://simplesamlphp.org/docs/stable/simplesamlphp-sp) (except enabling a certificate for your service provider, which should be done according to the instructions below).

- Edit `${project.root}/simplesamlphp/metadata/saml20-idp-remote.php` as described in [IdP remote metadata reference](https://simplesamlphp.org/docs/stable/simplesamlphp-reference-idp-remote).

#### <i class="icon-pencil"></i> Optional Config

If your Identity Provider/Federation requires that your Service Providers hold a certificate.
	
 1. Create a self-signed certificate in the `${project.root}/simplesamlphp/cert` directory: 
   ```
    cd simplesamlphp/cert
    openssl req -x509 -sha256 -nodes -days 3652 -newkey rsa:2048 -keyout saml.pem -out saml.crt
    ```
    
 2. Edit your `${project.root}/simplesamlphp/config/authsources.php` entry, and add references to your certificate:
    ```
    'default-sp' => array(
      'saml:SP',
      'privatekey' => 'saml.pem',
      'certificate' => 'saml.crt',
      'signature.algorithm' => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',
    ),
    ```

#### <i class="icon-check"></i> Check Config

Review `${project.root}/simplesamlphp/config/config.php` and set any values called for by your project requirements.

#### <i class="icon-code"></i> BLT Copy Config

Execute `blt simplesamlphp:config:build` to copy these configuration files to the SimpleSAMLphp library.

#### <i class="icon-provider-github"></i> Deploy Code
Commit your changes to your Git repository.