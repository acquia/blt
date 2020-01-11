<?php

namespace Acquia\Blt\Robo\Commands\Recipes;

use Acquia\Blt\Robo\BltTasks;

/**
 * Defines commands in the "recipes:scaffold:*" namespace.
 */
class ScaffoldCommand extends BltTasks {

  /**
   * Migrate a project from Drupal Scaffold to Composer Scaffold.
   *
   * @see https://www.drupal.org/docs/develop/using-composer/using-drupals-composer-scaffold#s-migrating-composer-scaffold
   *
   * @command recipes:scaffold:migrate
   */
  public function migrate() {
    $this->logger->notice("This command will migrate your project from Drupal Scaffold to Composer Scaffold by modifying your composer.json file. Note the following important prerequisites and caveats:");
    $this->logger->notice("  * Your project must already be on Drupal 8.8 or higher.");
    $this->logger->notice("  * This script will run `composer update`, so it's best to already have all dependencies updated before proceeding.");
    $this->logger->notice("  * Certain Drupal Scaffold customizations such as excluded files will be preserved, but more advanced configuration may need to be manually migrated.");
    $this->logger->notice("After finishing, be sure to commit all modified files to Git. If you wish to undo the changes, simply discard the modified files.");

    if (!$this->confirm("Ready to go?")) {
      return;
    }

    $repo_root = $this->getConfigValue('repo.root');
    $composer_filepath = $repo_root . '/composer.json';
    $composer_contents = json_decode(file_get_contents($composer_filepath), TRUE);
    $template_composer_contents = json_decode(file_get_contents($repo_root . '/vendor/acquia/blt/subtree-splits/blt-project/composer.json'), TRUE);

    // Set up the migration.
    $legacy_packages = [
      'drupal-composer/drupal-scaffold',
      'drupal/core',
      'webflo/drupal-core-strict',
    ];
    $legacy_dev_packages = [
      'webflo/drupal-core-require-dev',
    ];
    $new_packages = [
      'drupal/core-composer-scaffold' => '^8.8',
      'drupal/core-recommended' => '^8.8.0',
    ];
    $new_dev_packages = [];
    $new_config = $template_composer_contents['extra']['drupal-scaffold'];
    // Try to preserve some specific dependencies and configuration.
    if (isset($composer_contents['require']['drupal/core'])) {
      $new_packages['drupal/core-recommended'] = $composer_contents['require']['drupal/core'];
    }
    if (isset($composer_contents['extra']['drupal-scaffold']['excludes'])) {
      foreach ($composer_contents['extra']['drupal-scaffold']['excludes'] as $exclude) {
        $new_config['file-mapping']['[web-root]/' . $exclude] = FALSE;
      }
    }
    if (isset($composer_contents['require-dev']['webflo/drupal-core-require-dev'])) {
      $new_dev_packages['drupal/core-dev'] = $new_packages['drupal/core-recommended'];
    }

    // Time to go. Start by removing legacy packages and configuration.
    foreach ($legacy_packages as $package) {
      unset($composer_contents['require'][$package]);
    }
    foreach ($legacy_dev_packages as $package) {
      unset($composer_contents['require-dev'][$package]);
    }
    unset($composer_contents['extra']['drupal-scaffold']['initial']);
    unset($composer_contents['scripts']['drupal-scaffold']);
    // Add new packages and configuration.
    foreach ($new_packages as $package => $version) {
      $composer_contents['require'][$package] = $version;
    }
    foreach ($new_dev_packages as $package => $version) {
      $composer_contents['require-dev'][$package] = $version;
    }
    $composer_contents['extra']['drupal-scaffold'] = $new_config;

    // Apply the changes.
    file_put_contents($composer_filepath, json_encode($composer_contents, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
    // This just sorts the new dependencies in composer.json. It's shockingly
    // the least hacky way to do so.
    $this->taskExec("composer require drupal/core-composer-scaffold:{$new_packages['drupal/core-composer-scaffold']} --no-update")
      ->dir($repo_root)
      ->run();
    // This actually applies the changes.
    $this->taskExec('composer update')
      ->dir($repo_root)
      ->run();

    $this->logger->notice('Migration to Composer Scaffold complete. Be sure to git commit all modified files (especially composer.json and composer.lock).');
  }

}
