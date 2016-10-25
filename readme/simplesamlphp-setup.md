# SimpleSAMLphp Setup

To configure SimpleSAMLphp, perform the following steps after initially setting up BLT:

1. Execute `blt simplesamlphp:init`. This will perform the initial setup tasks including:
	* Adds the simplesamlphp_auth module as a project dependency.
  * Patches your .htaccess file to allow access to the simplesaml path.
	* Copies configuration files to `${project.root}/simplesamlphp`
	* Adds a simplesamlphp property to project.yml
	* Creates a symbolic link in the docroot to the web accessible directory of the simplesamlphp library.
	* Adds a settings.php file to the project's default settings directory.

1. Edit `${project.root}/simplesamlphp/config/config.php`
	* This file has been pre-populated with a code snippet recommended for Acquia Cloud Environments. You will need to edit the `$config` array for your local environment. 
	* Update your database name in `$ah_options`
		
			$ah_options = array(  
  			  'database_name' => '[DATABASE-NAME]',  
  			  'session_store' => array(  
    		    'prod' => 'memcache', // This can be either `memcache` or `database`  
    		    'test' => 'memcache', // This can be either `memcache` or `database`  
    		    'dev'  => 'database', // This can be either `memcache` or `database`  
  			  ),
			);
	* Update the following values int the `$config` array
	

			$config['technicalcontact_name'] = "Technical Contact Name";
			$config['technicalcontact_email'] = "email@example.com";
			$config['secretsalt'] = '[YOUR-SECERET-SALT]';
			$config['auth.adminpassword'] = '[ADMIN-PASSWORD]';

1. Edit `${project.root}/simplesamlphp/config/authsources.php`
1. Edit `${project.root}/simplesamlphp/metadata/saml20-idp-remote.php`
1. Execute `blt simplesamlphp:config:build` to copy these configuration files to the SimpleSAMLphp library.
1. Commit the changes.

