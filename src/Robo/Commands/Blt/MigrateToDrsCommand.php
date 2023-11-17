<?php

namespace Acquia\Blt\Robo\Commands\Blt;

use Acquia\Blt\Robo\BltTasks;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

/**
 * Defines MigrateToDrs command.
 */
class MigrateToDrsCommand extends BltTasks {

  /**
   * Settings warning.
   *
   * @var string
   * Warning text added to the end of settings.php to point people to the BLT
   * docs on how to include settings.
   */
  private string $bltSettingsWarning = <<<WARNING
require DRUPAL_ROOT . "/../vendor/acquia/blt/settings/blt.settings.php";
/**
 * IMPORTANT.
 *
 * Do not include additional settings here. Instead, add them to settings
 * included by `blt.settings.php`. See BLT's documentation for more detail.
 *
 * @link https://docs.acquia.com/blt/
 */
WARNING;

  /**
   * Settings warning.
   *
   * @var string
   * Warning text added to the end of settings.php to point people
   * to the Acquia Drupal Recommended Settings
   * docs on how to include settings.
   */
  private string $drsSettingsWarning = <<<WARNING
require DRUPAL_ROOT . "/../vendor/acquia/drupal-recommended-settings/settings/acquia-recommended.settings.php";
/**
 * IMPORTANT.
 *
 * Do not include additional settings here. Instead, add them to settings
 * included by `acquia-recommended.settings.php`. See Acquia's documentation for more detail.
 *
 * @link https://docs.acquia.com/
 */
WARNING;

  /**
   * Blt use statement.
   */
  private string $bltUseStmt = 'use Acquia\Blt\Robo\Common\EnvironmentDetector;';

  /**
   * Drs use statement.
   */
  private string $drsUseStmt = 'use Acquia\Drupal\RecommendedSettings\Helpers\EnvironmentDetector;';

  /**
   * Migrate BLT to use DRS.
   *
   * @command blt:migrate-drs
   *
   * @aliases migrate
   */
  public function migrateDrs(): void {
    $multiSites = $this->getConfigValue('multisites');
    if (!empty($multiSites)) {
      $this->io()->warning('This script will update settings.php and local.settings.php files from site [' . implode(',', $multiSites) . '] with following changes.');
      $this->io()->table(['File', 'Snippet to remove', 'Snippet to add'], [
        ['settings.php', $this->bltSettingsWarning, $this->drsSettingsWarning],
        ['local.settings.php', $this->bltUseStmt, $this->drsUseStmt],
      ]);
    }

    // Don't proceed further if aborted by user.
    if (!$this->confirm('Do you want to proceed with this change?')) {
      return;
    }
    // Loop through each site and process setting & local.settings file.
    foreach ($multiSites as $site) {
      $this->processSettingsFile($site);
    }
    $this->io->info('Required changes to use DRS plugin is completed successfully!');
  }

  /**
   * Process settings file from each site.
   *
   * @param string $site
   *   The site names.
   */
  private function processSettingsFile(string $site): void {
    $root = $this->getConfigValue('docroot');
    $sitePath = Path::join($root, 'sites/' . $site);

    // Update settings file content.
    $filesystem = new Filesystem();
    if ($filesystem->exists($sitePath . '/settings.php')) {
      $this->updateSettingsFile($sitePath . '/settings.php');
    }
    if ($filesystem->exists($sitePath . '/settings/local.settings.php')) {
      $this->updateLocalSettingsFile($sitePath . '/settings/local.settings.php');
    }
    if ($filesystem->exists($sitePath . '/settings/default.local.settings.php')) {
      $this->updateLocalSettingsFile($sitePath . '/settings/default.local.settings.php');
    }
  }

  /**
   * Update settings file.
   *
   * @param string $settingFile
   *   The settings file path.
   */
  private function updateSettingsFile(string $settingFile): void {
    $fileContent = file_get_contents($settingFile);

    // Let remove BLT require section from settings.php
    if (preg_match($this->bltSettingsWarning, $fileContent)) {
      $fileContent = str_replace($this->bltSettingsWarning, '', $fileContent);
      file_put_contents($settingFile, $fileContent);
    }
    // Let add DRS require section in settings.php
    if (!preg_match($this->drsSettingsWarning, $fileContent)) {
      $fileContent .= $this->drsSettingsWarning;
      file_put_contents($settingFile, $fileContent);
    }
  }

  /**
   * Update local settings file.
   *
   * @param string $localSettingsFile
   *   The local settings file path.
   */
  private function updateLocalSettingsFile(string $localSettingsFile): void {
    $fileContent = file_get_contents($localSettingsFile);
    $updatedFileContent = str_replace($this->bltUseStmt, $this->drsUseStmt, $fileContent);
    file_put_contents($localSettingsFile, $updatedFileContent);
  }

}
