<?php

use Acquia\Blt\Robo\Config\ConfigInitializer;
use Symfony\Component\Console\Input\ArgvInput;

$input = new ArgvInput($_SERVER['argv']);
$config_initializer = new ConfigInitializer($repo_root, $input);
$config = $config_initializer->initialize();

$name = substr($_SERVER['HTTP_HOST'],0, strpos($_SERVER['HTTP_HOST'],'.local'));
$acsf_sites = $config->get('acsf.sites');
if (in_array($name, $acsf_sites)) {
  /**
   * Database configuration.
   */
  $databases = array(
    'default' =>
      array(
        'default' =>
          array(
            'database' => "drupal_{$name}",
            'username' => 'drupal',
            'password' => 'drupal',
            'host' => 'localhost',
            'port' => '3306',
            'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
            'driver' => 'mysql',
            'prefix' => '',
          ),
      ),
  );

  $settings['file_public_path'] = "sites/default/files/$name";
  $settings['file_private_path'] = "$repo_root/files-private/$name";
}
