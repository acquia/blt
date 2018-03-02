<?php

namespace Acquia\Blt\Robo\Commands\Doctor;

use function json_decode;
use Symfony\Component\Yaml\Yaml;

/**
 *
 */
class DrupalVmCheck extends DoctorCheck {

  public function performAllChecks() {
    if ($this->getInspector()->isDrupalVmLocallyInitialized()) {
      $this->checkDrupalVm();
    }
  }

  /**
   * Checks Drupal VM configuration.
   */
  protected function checkDrupalVm() {
    $drupal_vm_config_file = $this->getConfigValue('vm.config');
    if (!file_exists($drupal_vm_config_file)) {
      $this->logProblem(__FUNCTION__ . ':init', "You have DrupalVM initialized, but $drupal_vm_config_file is missing.", 'error');
      return FALSE;
    }
    $drupal_vm_config = Yaml::parse(file_get_contents($drupal_vm_config_file));

    $result = $this->getExecutor()->drush('site:alias --format=json')->silent(TRUE)->run();
    $drush_aliases = json_decode($result->getMessage(), TRUE);
    $local_alias_id = '@' . $this->getConfigValue('drush.aliases.local');
    if ($local_alias_id !== '@self') {
      if (empty($drush_aliases[$local_alias_id])) {
        $this->logProblem(__FUNCTION__ . ":alias", [
          "The drush alias assigned to drush.aliases.local does not exist in your drush aliases file.",
          "  drush.aliases.local is set to @$local_alias_id",
        ], 'error');
      }
      else {
        $local_alias = $drush_aliases[$local_alias_id];
        $this->checkHost($local_alias, $drupal_vm_config, $local_alias_id);
        $this->checkUri($local_alias, $drupal_vm_config, $local_alias_id);
        $this->checkRoot($drupal_vm_config, $local_alias, $local_alias_id);
      }
    }
  }

  /**
   * @param $local_alias
   * @param $drupal_vm_config
   * @param $local_alias_id
   */
  protected function checkUri(
    $local_alias,
    $drupal_vm_config,
    $local_alias_id
  ) {
    $parsed_uri = parse_url($local_alias['uri']);
    if ($parsed_uri['host'] != $drupal_vm_config['vagrant_hostname']) {
      $this->logProblem(__FUNCTION__ . ":uri", [
        "uri for @$local_alias_id drush alias does not match vagrant_hostname for DrupalVM.",
        "  uri is set to {$local_alias['uri']} for @$local_alias_id",
        "  vagrant_hostname is set to {$drupal_vm_config['vagrant_hostname']} for DrupalVM.",
        "  {$local_alias['uri']} != {$drupal_vm_config['vagrant_hostname']}",
      ], 'error');
    }
  }

  /**
   * @param $drupal_vm_config
   * @param $local_alias
   * @param $local_alias_id
   */
  protected function checkRoot(
    $drupal_vm_config,
    $local_alias,
    $local_alias_id
  ) {
    $expected_root = $drupal_vm_config['drupal_composer_install_dir'] . '/docroot';
    if ($local_alias['root'] != $expected_root) {
      $this->logProblem(__FUNCTION__ . ":root", [
        "root for @$local_alias_id drush alias does not match docroot for DrupalVM.",
        "  root is set to {$local_alias['root']} for @$local_alias_id",
        "  docroot is set to $expected_root for DrupalVM.",
        "  {$local_alias['root']} != $expected_root",
      ], 'error');
    }
  }

  /**
   * @param $local_alias
   * @param $drupal_vm_config
   * @param $local_alias_id
   */
  protected function checkHost(
    $local_alias,
    $drupal_vm_config,
    $local_alias_id
  ) {
    if ('vagrant' != $_SERVER['USER'] && $local_alias['host'] != $drupal_vm_config['vagrant_hostname']) {
      $this->logProblem(__FUNCTION__ . ":host", [
        "host for @$local_alias_id drush alias does not match vagrant_hostname for DrupalVM.",
        "  host is set to {$local_alias['host']} for @$local_alias_id",
        "  vagrant_hostname is set to {$drupal_vm_config['vagrant_hostname']} for DrupalVM.",
        "  {$local_alias['host']} != {$drupal_vm_config['vagrant_hostname']}",
      ], 'error');
    }
  }

}
