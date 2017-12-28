<?php

namespace Acquia\Blt\Drush\Command;

use Symfony\Component\Yaml\Yaml;

class DrupalVmCheck extends DoctorCheck {

  /**
   * @var bool
   */
  protected $drupalVmEnabled = FALSE;

  /**
   * @var array*/
  protected $drupalVmConfig = [];

  /**
   * DrupalVmCheck constructor.
   */
  public function __construct(BltDoctor $doctor) {
    parent::__construct($doctor);
    $this->setDrupalVmEnabled();
    $this->setDrupalVmConfig();
  }

  /**
   * @return bool
   */
  public function isDrupalVmEnabled() {
    return $this->drupalVmEnabled;
  }

  /**
   *
   */
  public function setDrupalVmEnabled() {
    if (file_exists($this->doctor->getRepoRoot() . '/Vagrantfile')) {
      $this->drupalVmEnabled = TRUE;
    }
  }

  /**
   * Checks Drupal VM configuration.
   */
  protected function checkDrupalVm() {
    if ($this->drupalVmEnabled) {
      $passed = TRUE;
      $drupal_vm_config = $this->getDrupalVmConfigFile();
      if (!file_exists($this->doctor->getRepoRoot() . '/' . $drupal_vm_config)) {
        $this->doctor->logOutcome(__FUNCTION__ . ':init', "You have DrupalVM initialized, but $drupal_vm_config is missing.", 'error');
        $passed = FALSE;
      }
      else {
        $this->setDrupalVmConfig();
      }
      $local_alias_id = '@' . $this->config['drush']['aliases']['local'];
      if ($local_alias_id !== '@self') {
        if (empty($this->drushAliases[$local_alias_id])) {
          $this->doctor->logOutcome(__FUNCTION__ . ":alias", [
            "The drush alias assigned to drush.aliases.local does not exist in your drush aliases file.",
            "  drush.aliases.local is set to @$local_alias_id",
          ], 'error');
          $passed = FALSE;
        }
        else {
          $this->doctor->logOutcome(__FUNCTION__ . ':alias', "drush.aliases.local exists your drush aliases file.", 'info');
          $local_alias = $this->drushAliases[$local_alias_id];
          if ('vagrant' != $_SERVER['USER'] && $local_alias['host'] != $this->drupalVmConfig['vagrant_hostname']) {
            $this->doctor->logOutcome(__FUNCTION__ . ":host", [
              "host for @$local_alias_id drush alias does not match vagrant_hostname for DrupalVM.",
              "  host is set to {$local_alias['host']} for @$local_alias_id",
              "  vagrant_hostname is set to {$this->drupalVmConfig['vagrant_hostname']} for DrupalVM.",
              "  {$local_alias['host']} != {$this->drupalVmConfig['vagrant_hostname']}",
            ], 'error');
            $passed = FALSE;
          }
          $parsed_uri = parse_url($local_alias['uri']);
          if ($parsed_uri['host'] != $this->drupalVmConfig['vagrant_hostname']) {
            $this->doctor->logOutcome(__FUNCTION__ . ":uri", [
              "uri for @$local_alias_id drush alias does not match vagrant_hostname for DrupalVM.",
              "  uri is set to {$local_alias['uri']} for @$local_alias_id",
              "  vagrant_hostname is set to {$this->drupalVmConfig['vagrant_hostname']} for DrupalVM.",
              "  {$local_alias['uri']} != {$this->drupalVmConfig['vagrant_hostname']}",
            ], 'error');
            $passed = FALSE;
          }
          $expected_root = $this->drupalVmConfig['drupal_composer_install_dir'] . '/docroot';
          if ($local_alias['root'] != $expected_root) {
            $this->doctor->logOutcome(__FUNCTION__ . ":root", [
              "root for @$local_alias_id drush alias does not match docroot for DrupalVM.",
              "  root is set to {$local_alias['root']} for @$local_alias_id",
              "  docroot is set to $expected_root for DrupalVM.",
              "  {$local_alias['root']} != $expected_root",
            ], 'error');
            $passed = FALSE;
          }
        }
      }
    }
    if ($passed) {
      $this->doctor->logOutcome(__FUNCTION__, "Drupal VM is configured correctly.", 'info');
    }
  }

  /**
   * @return string
   */
  protected function getDrupalVmConfigFile() {
    // This is the only non-config "box/config.yml" entry.
    $drupal_vm_config = isset($this->config['vm']['config']) ? $this->config['vm']['config'] : 'box/config.yml';
    // Is there a way to calculate this "${repo.root}"? Removing for now.
    $drupal_vm_config = str_replace('${repo.root}', "", $drupal_vm_config);
    return $drupal_vm_config;
  }

  /**
   * @return array|mixed
   */
  protected function setDrupalVmConfig() {
    $this->drupalVmConfig = Yaml::parse(file_get_contents($this->doctor->getRepoRoot() . '/' . $this->getDrupalVmConfigFile()));

    return $this->drupalVmConfig;
  }
}
