<?php

namespace Acquia\Blt\Robo\Commands\Doctor;

/**
 *
 */
class SimpleSamlPhpCheck extends DoctorCheck {

  public function performAllChecks() {
    $this->checkSimpleSamlPhp();
  }

  /**
   * Performs a high level check of SimpleSAMLphp installation.
   */
  protected function checkSimpleSamlPhp() {
    if ($this->getConfig()->has('simplesamlphp')) {
      $lib_root = $this->getConfigValue('repo.root') . '/vendor/simplesamlphp/simplesamlphp';
      $config_root = $this->getConfigValue('repo.root') . '/simplesamlphp';

      // Check for the configurable files in docroot/simplesamlphp.
      if (!file_exists($config_root)) {
        $this->logProblem(__FUNCTION__, [
          "Simplesamlphp config directory is missing. $config_root",
          "",
          "Run `blt simplesamlphp:config:init` to create a config directory.",
        ], 'error');
      }

      // Check for the SimpleSAMLphp library in the vendor directory.
      if (!file_exists($lib_root)) {
        $this->logProblem(__FUNCTION__, [
          "The SimpleSAMLphp library was not found in the vendor directory.",
          "  Run `blt simplesamlphp:config:init` to add the library as a dependency.",
        ], 'error');
      }

      // Compare config files in $config_root and $lib_root.
      if (file_exists($lib_root) && file_exists($config_root)) {
        $config_files = [
          '/config/config.php',
          '/config/authsources.php',
          '/metadata/saml20-idp-remote.php',
        ];
        foreach ($config_files as $config_file) {
          if (file_exists($lib_root . $config_file) && file_exists($config_root . $config_file)) {
            $config_file_content = file_get_contents($config_root . $config_file);
            $lib_file_content = file_get_contents($lib_root . $config_file);
            if (strcmp($config_file_content, $lib_file_content) !== 0) {
              $this->logProblem(__FUNCTION__, [
                "The configuration file: $config_file in $config_root does not match the one in $lib_root.",
                "  Run `blt source:build:simplesamlphp-config` to copy the files from the repo root to the library.",
              ], 'error');
            }
          }
          else {
            $lib_file_path = $lib_root . $config_file;
            $this->logProblem(__FUNCTION__, [
              "$lib_file_path is missing. Run `blt source:build:simplesamlphp-config`.",
            ], 'error');
          }
        }
      }

      // Check that the library's www dirctory is symlinked in the docroot.
      if (!file_exists($this->getConfigValue('docroot') . '/simplesaml')) {
        $this->logProblem(__FUNCTION__, [
          "The symlink to the SimpleSAMLphp library is missing from your docroot.",
          "  Run `blt recipes:simplesamlphp:init`",
        ], 'error');
      }

      // Check that access to the symlinked directory is not blocked.
      $htaccess = file_get_contents($this->getConfigValue('docroot') . '/.htaccess');
      if (!strstr($htaccess, 'simplesaml')) {
        $this->logProblem(__FUNCTION__, [
          "Access to {$this->getConfigValue('docroot')}/simplesaml is blocked by .htaccess",
          "  Add the snippet in simplesamlphp-setup.md readme to your .htaccess file.",
        ], 'error');
      }
    }
  }

}
