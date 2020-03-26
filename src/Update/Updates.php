<?php

namespace Acquia\Blt\Update;

// The following line is required for annotations to work.
// phpcs:ignore
use Acquia\Blt\Annotations\Update;
use Acquia\Blt\Robo\Common\ArrayManipulator;

/**
 * Defines scripted updates for specific version deltas of BLT.
 */
class Updates {

  /**
   * Updater var.
   *
   * @var \Acquia\Blt\Update\Updater
   */
  protected $updater;

  /**
   * Updates constructor.
   *
   * @param \Acquia\Blt\Update\Updater $updater
   *   Updater.
   */
  public function __construct(Updater $updater) {
    $this->updater = $updater;
  }

  // phpcs:disable Drupal.NamingConventions.ValidFunctionName

  /**
   * Version 10.0.0.
   *
   * @Update(
   *    version = "10000000",
   *    description = "10.x Updates."
   * )
   */
  public function update_10000000() {
    $composer_json = $this->updater->getComposerJson();
    $template_composer_json = $this->updater->getTemplateComposerJson();
    $blt_composer_json = json_decode(file_get_contents($this->updater->getBltRoot() . '/composer.json'), TRUE);
    // Remove require-dev dependencies that are now defined in blt-require-dev.
    $blt_require_dev_composer_json = json_decode(file_get_contents($this->updater->getBltRoot() . '/subtree-splits/blt-require-dev/composer.json'), TRUE);
    foreach ($blt_require_dev_composer_json['require'] as $package_name => $version) {
      unset($composer_json['require-dev'][$package_name]);
    }

    // Ensure that suggested packages do not go missing.
    if (file_exists($this->updater->getRepoRoot() . "/blt/composer.suggested.json")) {
      $merge_plugin_require = $composer_json['extra']['merge-plugin']['require'];
      if (in_array("blt/composer.suggested.json", $merge_plugin_require)) {
        $composer_suggested = json_decode(file_get_contents($this->updater->getRepoRoot() . "/blt/composer.suggested.json"), TRUE);
        foreach ($composer_suggested['require'] as $package_name => $version_constraint) {
          // If it IS in template composer.json but NOT in root composer.json,
          // add it to root.
          if (!array_key_exists($package_name, $composer_json['require']) &&
              array_key_exists($package_name, $template_composer_json['require']) &&
              !array_key_exists($package_name, $blt_composer_json['require'])) {
            $composer_json['require'][$package_name] = $version_constraint;
          }
        }
      }
    }
    unset($composer_json['extra']['merge-plugin']);

    // Copy select config from composer.json template.
    $sync_composer_keys = [
      'autoload',
      'autoload-dev',
      'repositories',
      'extra',
      'scripts',
      'config',
    ];
    foreach ($sync_composer_keys as $sync_composer_key) {
      if (!array_key_exists($sync_composer_key, $composer_json)) {
        $composer_json[$sync_composer_key] = [];
      }
      $composer_json[$sync_composer_key] = ArrayManipulator::arrayMergeRecursiveDistinct($composer_json[$sync_composer_key],
        $template_composer_json[$sync_composer_key]);
    }

    // Require blt-require-dev.
    $composer_json['require-dev']['acquia/blt-require-dev'] = $template_composer_json['require-dev']['acquia/blt-require-dev'];

    $this->updater->writeComposerJson($composer_json);

    // Remove vestigial files.
    $this->updater->deleteFile([
      $this->updater->getRepoRoot() . "/blt/composer.required.json",
      $this->updater->getRepoRoot() . "/blt/composer.suggested.json",
      $this->updater->getRepoRoot() . "/blt/composer.overrides.json",
    ]);
    $messages[] = "Your composer.json file has been modified to remove the Composer merge plugin.";
    $messages[] = "You must execute `composer update --lock` to update your lock file.";

    // Updates to setting and configuration files for BLT 10.0.x.
    $messages[] = "";
    $messages[] = "BLT 10 includes many changes to configuration and settings files. These will now be regenerated.";

    // Check for presence of factory-hooks directory. Regenerate if present.
    if (file_exists($this->updater->getRepoRoot() . '/factory-hooks')) {
      $messages[] = "Factory Hooks (/factory-hooks) have been regenerated. Review the resulting file(s) and re-add any customizations.";
      $this->updater::executeCommand("./vendor/bin/blt recipes:acsf:init:hooks", NULL, FALSE);
    }

    if ($this->updater->regenerateCloudHooks()) {
      $messages[] = "Cloud Hooks (/hooks) have been regenerated. Review the resulting file(s) and re-add any customizations.";
    }

    // Check for presence of acquia-pipelines.yml file. Regenerate if present.
    if (file_exists($this->updater->getRepoRoot() . '/acquia-pipelines.yml')) {
      $messages[] = "acquia-pipelines.yml has been regenerated. Review the resulting file and re-add any customizations.";
      $this->updater::executeCommand("./vendor/bin/blt recipes:ci:pipelines:init", NULL, FALSE);
    }

    // Check for presence of .travis.yml files. Regenerate if present.
    if (file_exists($this->updater->getRepoRoot() . '/.travis.yml')) {
      $messages[] = ".travis.yml has been regenerated. Review the resulting file and re-add any customizations..";
      $this->updater::executeCommand("./vendor/bin/blt recipes:ci:travis:init", NULL, FALSE);
    }

    // Regenerate local settings files.
    $messages[] = "Local settings files have been regenerated. Review the resulting file(s) and re-add any customizations..";
    $this->updater::executeCommand("./vendor/bin/blt blt:init:settings", NULL, FALSE);

    $formattedBlock = $this->updater->getFormatter()->formatBlock($messages, 'ice');
    $this->updater->getOutput()->writeln("");
    $this->updater->getOutput()->writeln($formattedBlock);
    $this->updater->getOutput()->writeln("");

    $project_config = $this->updater->getProjectYml();
    // Move 'reports' to subkey of 'tests'.
    if (!empty($project_config['reports'])) {
      $project_config['tests']['reports'] = $project_config['reports'];
      unset($project_config['reports']);
    }
    // Move 'phpunit' to subkey of 'tests'.
    if (!empty($project_config['phpunit'])) {
      $project_config['tests']['phpunit'] = $project_config['phpunit'];
      unset($project_config['phpunit']);
    }
    // Move 'behat.selenium' and 'behat.chrome' to subkey of 'tests'.
    if (!empty($project_config['behat']['selenium'])) {
      $project_config['tests']['selenium'] = $project_config['behat']['selenium'];
      unset($project_config['behat']['selenium']);
    }
    if (!empty($project_config['behat']['chrome'])) {
      $project_config['tests']['chrome'] = $project_config['behat']['chrome'];
      unset($project_config['behat']['chrome']);
    }
    $this->updater->writeProjectYml($project_config);

  }

  /**
   * Version 10.0.0.
   *
   * @Update(
   *    version = "10000001",
   *    description = "Move Drupal modules to project composer.json."
   * )
   */
  public function update_10000001() {
    $composer_json = $this->updater->getComposerJson();
    $template_composer_json = $this->updater->getTemplateComposerJson();
    foreach ($template_composer_json['require'] as $package_name => $package_version) {
      if (empty($composer_json['require'][$package_name])) {
        $composer_json['require'][$package_name] = $package_version;
      }
    }
    $this->updater->writeComposerJson($composer_json);
  }

  /**
   * Version 10.0.0.
   *
   * @Update(
   *    version = "10000002",
   *    description = "Regenerate cloud hooks if necessary."
   * )
   */
  public function update_10000002() {
    if ($this->updater->regenerateCloudHooks()) {
      $this->updater->getOutput()->writeln("Cloud Hooks have been updated. Review the resulting file(s) and ensure that any customizations have been re-added.");
    }
  }

  /**
   * Version 10.1.0.
   *
   * @Update(
   *    version = "10001000",
   *    description = "Remove composer autoload optimizations."
   * )
   */
  public function update_10001000() {
    $composer_json = $this->updater->getComposerJson();
    unset($composer_json['config']['apcu-autoloader']);
    unset($composer_json['config']['optimize-autoloader']);
    $this->updater->writeComposerJson($composer_json);
  }

  /**
   * Version 10.3.0.
   *
   * @Update(
   *   version = "10003000",
   *   description = "Regenerate acquia-pipelines.yml."
   * )
   */
  public function update_10003000() {
    $this->updater->regeneratePipelines();
  }

  /**
   * Version 10.4.0.
   *
   * @Update(
   *   version = "10004000",
   *   description = "Migrate to acquia/memcache-settings."
   * )
   */
  public function update_10004000() {
    $composer_json = $this->updater->getComposerJson();
    if (array_key_exists('drupal/memcache', $composer_json['require'])) {
      unset($composer_json['require']['drupal/memcache']);
      $composer_json['require']['acquia/memcache-settings'] = '*';
      $this->updater->writeComposerJson($composer_json);
      $this->updater->getOutput()->writeln("Memcache settings have moved from acquia/blt to acquia/memcache-settings, a separate Composer package, and additionally have been updated to use the stable Memcache 2.0 release. Your composer.json has been updated to depend on acquia/memcache-settings.");
      $this->updater->getOutput()->writeln("");
      $this->updater->getOutput()->writeln("You must run `composer update acquia/memcache-settings drupal/memcache` and commit the resulting changes to composer.json and composer.lock if you wish to use these updated settings. Otherwise, you will need to provide your own Memcache settings in docroot/sites/settings. See the release notes for additional details.");
      $this->updater->getOutput()->writeln("");
    }
  }

  /**
   * Version 11.0.0.
   *
   * @Update(
   *   version = "11000000",
   *   description = "Update blt-require-dev version."
   * )
   */
  public function update_11000000() {
    $composer_json = $this->updater->getComposerJson();
    $template_composer_json = $this->updater->getTemplateComposerJson();
    if (array_key_exists('acquia/blt-require-dev', $composer_json['require-dev'])) {
      $composer_json['require-dev']['acquia/blt-require-dev'] = $template_composer_json['require-dev']['acquia/blt-require-dev'];
      $this->updater->writeComposerJson($composer_json);
      $this->updater->getOutput()->writeln("acquia/blt-require-dev version has been updated in composer.json. You must run `composer update` and commit both composer.json and composer.lock to apply the changes.");
    }
  }

  /**
   * Version 11.0.0.
   *
   * @Update(
   *   version = "11000001",
   *   description = "Move Drupal Scaffold to project composer.json."
   * )
   */
  public function update_11000001() {
    $composer_json = $this->updater->getComposerJson();
    if (!array_key_exists('drupal-composer/drupal-scaffold', $composer_json['require'])) {
      $composer_json['require']['drupal-composer/drupal-scaffold'] = "^2.5.4";
      $this->updater->writeComposerJson($composer_json);
      $this->updater->getOutput()->writeln("Drupal Scaffold has been added to your composer.json. You must run `composer update` and commit both composer.json and composer.lock to apply the changes.");
    }
  }

  /**
   * Version 11.0.2.
   *
   * @Update(
   *   version = "11000020",
   *   description = "Update phpcs.xml.dist."
   * )
   */
  public function update_11000020() {
    $this->updater->syncWithTemplate('phpcs.xml.dist', TRUE);
    $this->updater->getOutput()->writeln("phpcs.xml.dist has been updated to accommodate changes in Coder 8.3.7. You should review and commit the changes.");
    if (file_exists($this->updater->getRepoRoot() . '/phpcs.xml')) {
      $this->updater->getOutput()->writeln('Also review phpcs.xml.dist for changes that should be copied to your custom phpcs.xml');
    }
  }

  /**
   * Version 11.2.0.
   *
   * @Update(
   *   version = "11002000",
   *   description = "Update .gitignore with travis_wait."
   * )
   */
  public function update_11002000() {
    $filename = $this->updater->getRepoRoot() . '/.gitignore';
    $lines = file($filename);
    if (!in_array("/travis_wait*\n", $lines)) {
      file_put_contents($filename, "\n# BLT 11.2.0 update to support simulated deploys\n/travis_wait*\n", FILE_APPEND);
    }
  }

  /**
   * Version 11.4.0.
   *
   * @Update(
   *   version = "11004000",
   *   description = "Regenerate factory hooks from updated templates."
   * )
   */
  public function update_11004000() {
    if (file_exists($this->updater->getRepoRoot() . '/factory-hooks')) {
      $messages = [
        "Factory Hooks have been regenerated in your existing /factory-hooks directory.",
        "Review the resulting file(s) and re-add any customizations.",
      ];
      $this->updater->executeCommand("./vendor/bin/blt recipes:acsf:init:hooks");
      $formattedBlock = $this->updater->getFormatter()->formatBlock($messages, 'ice');
      $this->updater->getOutput()->writeln("");
      $this->updater->getOutput()->writeln($formattedBlock);
      $this->updater->getOutput()->writeln("");
    }
  }

}
