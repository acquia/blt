<?php

namespace Acquia\Blt\Robo\LocalEnvironment;

use Acquia\Blt\Robo\Common\ArrayManipulator;
use Acquia\Blt\Robo\Common\IO;
use Acquia\Blt\Robo\Config\ConfigAwareTrait;
use Drupal\Core\Installer\Exception\AlreadyInstalledException;
use Grasmash\YamlExpander\Expander;
use Robo\Contract\ConfigAwareInterface;

/**
 * Class LocalEnvironment
 * @package Acquia\Blt\Robo\Common
 */
class LocalEnvironment implements ConfigAwareInterface {

  use IO;
  use ConfigAwareTrait;

  /**
   *
   */
  public function setDrupalSettingsFile() {
    $this->drupalSettingsFile = $this->getConfigValue('docroot') . '/docroot/sites/default/settings.php';
  }

  /**
   * @return bool
   */
  public function repoRootExists() {
    return file_exists($this->getConfigValue('repo.root'));
  }

  /**
   * @return bool
   */
  public function docrootExists() {
    return file_exists($this->getConfigValue('docroot'));
  }

  /**
   * @return bool
   */
  public function drupalSettingsFileExists($settings_file_path) {
    if (!file_exists($settings_file_path)) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * @return bool
   */
  public function drupalSettingsFileIsValid($settings_file_path) {
    $settings_file_contents = file_get_contents($settings_file_path);
    if (!strstr($settings_file_contents,
      '/../vendor/acquia/blt/settings/blt.settings.php')
    ) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Checks that Drupal is installed.
   */
  public function drupalIsInstalled() {
    $docroot = $this->getConfigValue('docroot');
    try {
      require $docroot . '/core/includes/install.core.inc';
      require $docroot . '/core/includes/install.inc';
      require $docroot . '/core/modules/system/system.install';
      install_verify_database_ready();
    }
    catch (AlreadyInstalledException $e) {
      return TRUE;
    }
    catch (\Exception $e) {

    }
    catch (\Throwable $e) {

    }

    return FALSE;
  }

  /**
   * Checks if a given command exists on the system.
   *
   * @param $command string the command binary only. E.g., "drush" or "php".
   *
   * @return bool
   *   TRUE if the command exists, otherwise FALSE.
   */
  public function commandExists($command) {
    exec("command -v $command >/dev/null 2>&1", $output, $exit_code);
    return $exit_code == 0;
  }

  public function behatIsConfigured() {
    return file_exists($this->getConfigValue('repo.root') . '/tests/behat/local.yml');
  }
}
