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
   * An array of search replace pairs.
   *
   * @var array|\string[][]
   */
  private array $searchReplacePairs = [
    [
      'search' => 'require DRUPAL_ROOT . "/../vendor/acquia/blt/settings/blt.settings.php";',
      'replace' => 'require DRUPAL_ROOT . "/../vendor/acquia/drupal-recommended-settings/settings/acquia-recommended.settings.php";',
      'file' => 'settings.php',
    ],
    [
      'search' => <<<BLT_WARNING
/**
 * IMPORTANT.
 *
 * Do not include additional settings here. Instead, add them to settings
 * included by `blt.settings.php`. See BLT's documentation for more detail.
 *
 * @link https://docs.acquia.com/blt/
 */
BLT_WARNING,
      'replace' => <<<DRS_WARNING
/**
 * IMPORTANT.
 *
 * Do not include additional settings here. Instead, add them to settings
 * included by `acquia-recommended.settings.php`. See Acquia's documentation for more detail.
 *
 * @link https://docs.acquia.com/
 */
DRS_WARNING,
      'file' => 'settings.php',
    ],
    [
      'search' => 'use Acquia\Blt\Robo\Common\EnvironmentDetector;',
      'replace' => 'use Acquia\Drupal\RecommendedSettings\Helpers\EnvironmentDetector;',
      'file' => 'settings.php',
    ],
    [
      'search' => 'blt_override_config_directories',
      'replace' => 'drs_override_config_directories',
      'file' => 'local.settings.php',
    ],
  ];

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
      $rows = array_map(function ($pair) {
        return [
          $pair["file"],
          $pair["search"],
          $pair["replace"],
        ];
      }, $this->searchReplacePairs);
      $this->io()->warning('This script will update following files from site [' . implode(',', $multiSites) . '] with following changes.');
      $this->io()->table(['File', 'Snippet to remove', 'Snippet to add'], $rows);
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

    // Relative paths of settings.php file.
    $relativePaths = [
      "$sitePath/settings.php",
      "$sitePath/settings/local.settings.php",
      "$sitePath/settings/default.local.settings.php",
    ];
    $filesystem = new Filesystem();
    foreach ($relativePaths as $relativePath) {
      if ($filesystem->exists($relativePath)) {
        // Update settings file content.
        $this->updateSettingsFile($relativePath);
      }
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

    // Loop through the search/replace pairs.
    foreach ($this->searchReplacePairs as $pair) {
      // Check if the search string exists in the file content.
      if (strpos($fileContent, $pair['search']) !== FALSE) {
        // Replace the search string with the replace string.
        $fileContent = str_replace($pair['search'], $pair['replace'], $fileContent);
      }
    }

    // Write the modified content back to the file.
    file_put_contents($settingFile, $fileContent);
  }

}
