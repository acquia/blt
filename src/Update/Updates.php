<?php

namespace Acquia\Blt\Update;

use Acquia\Blt\Annotations\Update;
use Acquia\Blt\Robo\Common\ArrayManipulator;

/**
 * Defines scripted updates for specific version deltas of BLT.
 */
class Updates {

  /**
   * @var \Acquia\Blt\Update\Updater
   */
  protected $updater;

  /**
   * Updates constructor.
   *
   * @param \Acquia\Blt\Update\Updater $updater
   */
  public function __construct(Updater $updater) {
    $this->updater = $updater;
  }

  /**
   * 8.5.1.
   *
   * @Update(
   *   version = "8005001",
   *   description = "Removes deprecated features patch."
   * )
   */
  public function update_8005001() {
    $this->updater->removeComposerPatch("drupal/features",
      "https://www.drupal.org/files/issues/features-2808303-2.patch");
  }

  /**
   * 8.6.0.
   *
   * @Update(
   *   version = "8006000",
   *   description = "Moves configuration files to blt subdirectory. Removes .git/hooks symlink."
   * )
   */
  public function update_8006000() {
    // Move files to blt subdir.
    $this->updater->moveFile('project.yml', 'blt/project.yml', TRUE);
    $this->updater->moveFile('project.local.yml', 'blt/project.local.yml',
      TRUE);
    $this->updater->moveFile('example.project.local.yml',
      'blt/example.project.local.yml', TRUE);

    // Delete symlink to hooks directory. Individual git hooks are now symlinked, not the entire directory.
    $this->updater->deleteFile('.git/hooks');
    $this->updater->getOutput()
      ->writeln('.git/hooks was deleted. Please re-run setup:git-hooks to install git hooks locally.');

    $this->updater->removeComposerRepository('https://github.com/mortenson/composer-patches');
    $this->updater->removeComposerScript('post-create-project-cmd');

    // Change 'deploy' module key to 'prod'.
    // @see https://github.com/acquia/blt/pull/700.
    $project_config = $this->updater->getProjectYml();
    if (!empty($project_config['modules']['deploy'])) {
      $project_config['modules']['prod'] = $project_config['modules']['deploy'];
      unset($project_config['modules']['deploy']);
    }

    $this->updater->getOutput()
      ->writeln("<comment>You MUST remove .travis.yml and re-initialize Travis CI support with `blt ci:travis:init`.</comment>");
  }

  /**
   * 8.6.2.
   *
   * @Update(
   *   version = "8006002",
   *   description = "Updates composer.json version constraints for Drupal.org."
   * )
   */
  public function update_8006002() {
    $composer_json = $this->updater->getComposerJson();
    $composer_json = DoPackagistConverter::convertComposerJson($composer_json);
    // This package is not compatible with D.O style version constraints.
    unset($composer_json['require']['drupal-composer/drupal-security-advisories']);
    $this->updater->writeComposerJson($composer_json);
  }

  /**
   * 8.5.4.
   *
   * @Update(
   *   version = "8006004",
   *   description = "Removes deprecated packages from composer.json."
   * )
   */
  public function update_8006004() {
    $composer_json = $this->updater->getComposerJson();
    $remove_packages = [
      'drupal/coder',
      'drupal-composer/drupal-security-advisories',
      'phing/phing',
      'phpunit/phpunit',
      'behat/mink-extension',
      'behat/mink-goutte-driver',
      'behat/mink-browserkit-driver',
    ];
    foreach ($remove_packages as $package) {
      unset($composer_json['require'][$package]);
      unset($composer_json['require-dev'][$package]);
    }
    $this->updater->writeComposerJson($composer_json);
  }

  /**
   * 8.6.6.
   *
   * @Update(
   *   version = "8006006",
   *   description = "Removes drush/drush from require-dev."
   * )
   */
  public function update_8006006() {
    $composer_json = $this->updater->getComposerJson();
    unset($composer_json['require-dev']['drush/drush']);
    $this->updater->writeComposerJson($composer_json);
  }

  /**
   * 8.6.7.
   *
   * @Update(
   *   version = "8006007",
   *   description = "Changes drupal scaffold excludes from associative to indexed array."
   * )
   */
  public function update_8006007() {
    $composer_json = $this->updater->getComposerJson();
    if (!empty($composer_json['extra']['drupal-scaffold']['excludes'])) {
      $composer_json['extra']['drupal-scaffold']['excludes'] = array_unique(array_values($composer_json['extra']['drupal-scaffold']['excludes']));
    }
    $this->updater->writeComposerJson($composer_json);
  }

  /**
   * 8.6.12.
   *
   * @Update(
   *   version = "8006012",
   *   description = "Removes lightning patch."
   * )
   */
  public function update_8006012() {
    $this->updater->removeComposerPatch("acquia/lightning",
      "https://www.drupal.org/files/issues/2836258-3-lightning-extension-autoload.patch");
  }

  /**
   * 8.7.0.
   *
   * @Update(
   *   version = "8007000",
   *   description = "Updating composer.json to use wikimedia composer-merge-plugin."
   * )
   */
  public function update_8007000() {
    $composer_required_json = $this->updater->getComposerRequiredJson();
    $composer_suggested_json = $this->updater->getComposerSuggestedJson();
    $composer_json = $this->updater->getComposerJson();

    // Remove deprecated config.
    unset($composer_json['extra']['blt']['composer-exclude-merge']);

    // Remove packages from root composer.json that are already defined in BLT's composer.required.json with matching version.
    if (!empty($composer_required_json['require'])) {
      foreach ($composer_required_json['require'] as $package_name => $package_version) {
        if (array_key_exists($package_name,
            $composer_json['require']) && $package_version == $composer_json['require'][$package_name]
        ) {
          unset($composer_json['require'][$package_name]);
        }
      }
    }

    // Do the same for require-dev.
    if (!empty($composer_required_json['require-dev']) && !empty($composer_json['require-dev'])) {
      foreach ($composer_required_json['require-dev'] as $package_name => $package_version) {
        if (array_key_exists($package_name,
            $composer_json['require-dev']) && $package_version == $composer_json['require-dev'][$package_name]
        ) {
          unset($composer_json['require-dev'][$package_name]);
        }
      }
    }

    // Remove packages from root composer.json that are already defined in BLT's composer.suggested.json with matching version.
    if (!empty($composer_suggested_json['require'])) {
      foreach ($composer_suggested_json['require'] as $package_name => $package_version) {
        if (array_key_exists($package_name,
            $composer_json['require']) && $package_version == $composer_json['require'][$package_name]
        ) {
          unset($composer_json['require'][$package_name]);
        }
      }
    }
    // Do the same for require-dev.
    if (!empty($composer_suggested_json['require-dev'])) {
      foreach ($composer_suggested_json['require-dev'] as $package_name => $package_version) {
        if (array_key_exists($package_name,
            $composer_json['require-dev']) && $package_version == $composer_json['require-dev'][$package_name]
        ) {
          unset($composer_json['require-dev'][$package_name]);
        }
      }
    }

    // Remove redundant config for drupal-scaffold.
    if (!empty($composer_json['extra']['drupal-scaffold']) && !empty($composer_required_json['extra']['drupal-scaffold']) &&
      $composer_json['extra']['drupal-scaffold'] == $composer_required_json['extra']['drupal-scaffold']) {
      unset($composer_json['extra']['drupal-scaffold']);
    }

    // Remove redundant config for autoload-dev.
    unset($composer_json['autoload-dev']['psr-4']['Drupal\\Tests\\PHPUnit\\']);
    if (empty($composer_json['autoload-dev']['psr-4'])) {
      unset($composer_json['autoload-dev']['psr-4']);
    }
    if (empty($composer_json['autoload-dev'])) {
      unset($composer_json['autoload-dev']);
    }

    if (!empty($composer_json['scripts'])) {
      foreach ($composer_required_json['scripts'] as $script_name => $script) {
        if (array_key_exists($script_name, $composer_json['scripts'])) {
          unset($composer_json['scripts'][$script_name]);
        }
      }
      if (empty($composer_json['scripts'])) {
        unset($composer_json['scripts']);
      }
    }

    $template_composer_json = $this->updater->getTemplateComposerJson();
    // Set installer-paths to match template.
    foreach ($template_composer_json['extra']['installer-paths'] as $key => $template_installer_path) {
      $composer_json['extra']['installer-paths'][$key] = $template_installer_path;
    }

    // Set wikimedia/composer-merge-plugin config.
    if (!empty($composer_json['extra']['merge-plugin'])) {
      $composer_json['extra']['merge-plugin'] = ArrayManipulator::arrayMergeRecursiveDistinct($composer_json['extra']['merge-plugin'], $template_composer_json['extra']['merge-plugin']);
    }
    else {
      $composer_json['extra']['merge-plugin'] = $template_composer_json['extra']['merge-plugin'];
    }

    // Write to file.
    $this->updater->writeComposerJson($composer_json);

    $messages = [
      'BLT will no longer directly modify your composer.json requirements!',
      "Default composer.json values from BLT are now merged into your root composer.json via wikimedia/composer-merge-plugin. Please see the following documentation for more information:\n",
      "  - http://blt.readthedocs.io/en/8.x/readme/updating-blt/#modifying-blts-default-composer-values\n   - https://github.com/wikimedia/composer-merge-plugin"
    ];
    $formattedBlock = $this->updater->getFormatter()->formatBlock($messages, 'ice');

    $this->updater->getOutput()->writeln("");
    $this->updater->getOutput()->writeln($formattedBlock);
    $this->updater->getOutput()->writeln("");
    $this->updater->getOutput()->writeln("<comment>Please execute `composer update` to incorporate these final automated changes to composer.json.</comment>");

    // Sync updates to drushrc.php manually since it has been added to ignore-existing.txt.
    $drushrcFile = 'drush/drushrc.php';
    $this->updater->syncWithTemplate($drushrcFile, TRUE);

    // Legacy versions will have defaulted to use features for config management.
    // Must explicitly set formerly assumed value.
    $project_yml = $this->updater->getProjectYml();
    $project_yml['cm']['strategy'] = 'features';
    $this->updater->writeProjectYml($project_yml);
  }
}
