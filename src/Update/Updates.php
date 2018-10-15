<?php

namespace Acquia\Blt\Update;

use Acquia\Blt\Annotations\Update;
use Acquia\Blt\Robo\Common\ArrayManipulator;
use Dflydev\DotAccessData\Data;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use Acquia\Blt\Robo\Common\ComposerMunge;

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
      ->writeln('.git/hooks was deleted. Please re-run blt:init:git-hooks to install git hooks locally.');

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
      ->writeln("<comment>You MUST remove .travis.yml and re-initialize Travis CI support with `blt recipes:ci:travis:init`.</comment>");
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
      "  - http://blt.readthedocs.io/en/9.x/readme/updating-blt/#modifying-blts-default-composer-values\n   - https://github.com/wikimedia/composer-merge-plugin"
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

  /**
   * 8.9.0.
   *
   * @Update(
   *   version = "8009000",
   *   description = "Updating deprecated yaml keys."
   * )
   */
  public function update_8009000() {
    $project_yml = $this->updater->getProjectYml();
    if (!empty($project_yml['behat']['launch-phantomjs']) && $project_yml['behat']['launch-phantomjs']) {
      $project_yml['behat']['web-driver'] = 'phantomjs';
    }
    else {
      $project_yml['behat']['web-driver'] = 'selenium';
    }
    unset($project_yml['behat']['launch-selenium']);
    unset($project_yml['behat']['launch-phantomjs']);

    if (!empty($project_yml['multisite.name'])) {
      $project_yml['multisites'][] = $project_yml['multisite.name'];
      unset ($project_yml['multisite.name']);
    }
    unset($project_yml['import']);

    if (file_exists($this->updater->getRepoRoot() . '/Vagrantfile')) {
      $project_yml['vm']['enable'] = TRUE;
    }

    $this->updater->writeProjectYml($project_yml);

    $messages = [
      "You have updated to a new major version of BLT, which introduces backwards-incompatible changes.",
      "You may need to perform the following manual update steps:",
      "  - View the full list of commands via `blt list`, <comment>BLT commands have changed</comment>",
      "  - Re-initialize default Travis CI configuration via `blt recipes:ci:travis:init`.
         - Re-initialize default Acquia Pipelines configuration via `blt recipes:ci:pipelines:init`.",
      "  - Port custom Phing commands to Robo. All Phing commands are now obsolete. See:",
      "    http://blt.readthedocs.io/en/9.x/readme/extending-blt/",
    ];
    if (file_exists($this->updater->getRepoRoot() . '/blt/composer.overrides.json')) {
      $messages[] = "  - <comment>blt/composer.overrides.json</comment> is no longer necessary.";
      $messages[] = "  -  Move your overrides to your root composer.json, and set extra.merge-plugin.ignore-duplicates to true.";
    }
    $formattedBlock = $this->updater->getFormatter()->formatBlock($messages, 'ice');
    $this->updater->getOutput()->writeln("");
    $this->updater->getOutput()->writeln($formattedBlock);
    $this->updater->getOutput()->writeln("");
  }

  /**
   * 8.9.1.
   *
   * @Update(
   *   version = "8009001",
   *   description = "Removing deprecated filesets."
   * )
   */
  public function update_8009001() {
    $project_yml = $this->updater->getProjectYml();
    unset($project_yml['phpcs']['filesets']['files.php.custom.modules']);
    unset($project_yml['phpcs']['filesets']['files.php.custom.themes']);
    unset($project_yml['phpcs']['filesets']['files.php.tests']);
    $this->updater->writeProjectYml($project_yml);
  }

  /**
   * 8.9.3.
   *
   * @Update(
   *    version = "8009003",
   *    description = "Adding support for Asset Packagist."
   * )
   */
  public function update_8009003() {
    $composer_json = $this->updater->getComposerJson();

    $composer_json['extra']['installer-types'][] = 'bower-asset';
    $composer_json['extra']['installer-types'][] = 'npm-asset';
    $composer_json['extra']['installer-paths']['docroot/libraries/{$name}'][] = 'type:bower-asset';
    $composer_json['extra']['installer-paths']['docroot/libraries/{$name}'][] = 'type:npm-asset';

    // Add the Asset Packagist repository if it does not already exist.
    if (isset($composer_json['repositories'])) {
      $repository_key = NULL;
      foreach ($composer_json['repositories'] as $key => $repository) {
        if ($repository['type'] == 'composer' && strpos($repository['url'], 'https://asset-packagist.org') === 0) {
          $repository_key = $key;
          break;
        }
      }
      if (is_null($repository_key)) {
        $composer_json['repositories']['asset-packagist'] = [
          'type' => 'composer',
          'url' => 'https://asset-packagist.org',
        ];
      }
    }

    $projectAcsfHooks = $this->updater->getRepoRoot() . '/factory-hooks';
    $acsf_inited = file_exists($projectAcsfHooks);
    if ($acsf_inited) {
      $composer_json['config']['platform']['php'] = '5.6';
    }

    $this->updater->writeComposerJson($composer_json);

    $messages = [
      "Your composer.json file has been modified to be compatible with Lightning 2.1.8+.",
      "You must execute `composer update` to update your lock file.",
    ];
    $formattedBlock = $this->updater->getFormatter()->formatBlock($messages, 'ice');
    $this->updater->getOutput()->writeln("");
    $this->updater->getOutput()->writeln($formattedBlock);
    $this->updater->getOutput()->writeln("");
  }

  /**
   * 8.9.7.
   *
   * @Update(
   *    version = "8009007",
   *    description = "Removing drush files."
   * )
   */
  public function update_8009007() {
    $this->updater->deleteFile('drush.wrapper');
    $this->updater->deleteFile('.drush-use');

    // Recommend drush upgrade.
    $messages = [
      "You should replace your local global installation of drush with drush launcher:",
      "https://github.com/drush-ops/drush-launcher",
    ];
    $formattedBlock = $this->updater->getFormatter()->formatBlock($messages, 'ice');
    $this->updater->getOutput()->writeln("");
    $this->updater->getOutput()->writeln($formattedBlock);
    $this->updater->getOutput()->writeln("");
  }

  /**
   * 8.9.11.
   *
   * @Update(
   *    version = "8009011",
   *    description = "Move vm.enable from project.yml to project.local.yml."
   * )
   */
  public function update_8009011() {
    $project_yml = $this->updater->getProjectYml();
    if (isset($project_yml['vm']['enable'])) {
      // Add to project.local.yml.
      $project_local_yml = $this->updater->getProjectLocalYml();
      $project_local_yml['vm']['enable'] = $project_yml['vm']['enable'];
      $this->updater->writeProjectLocalYml($project_local_yml);
      // Remove from project.yml.
      unset($project_yml['vm']);
      $this->updater->writeProjectYml($project_yml);
    }

  }

  /**
   * 9.0.0.
   *
   * @Update(
   *    version = "9000000",
   *    description = "Convert Drush 8 files to Drush 9."
   * )
   */
  public function update_9000000() {
    $messages = [];
    $this->updater->syncWithTemplate('.gitignore', TRUE);
    $this->updater->syncWithTemplate('phpcs.xml.dist', TRUE);
    if (file_exists($this->updater->getRepoRoot() . '/phpcs.xml')) {
      $messages[] = 'phpcs.xml.dist has been updated. Review it for changes that should be copied to your custom phpcs.xml';
    }
    $this->updater->syncWithTemplate('tests/behat/example.local.yml', TRUE);
    $this->updater->moveFile('drush/site-aliases/aliases.drushrc.php', 'drush/site-aliases/legacy.aliases.drushrc.php');
    $this->updater->replaceInFile('drush/site-aliases/legacy.aliases.drushrc.php', "' . drush_server_home() . '", '$HOME');
    $process = new Process(
      "./vendor/bin/drush site:alias-convert {$this->updater->getRepoRoot()}/drush/sites --sources={$this->updater->getRepoRoot()}/drush/site-aliases",
      $this->updater->getRepoRoot()
    );
    $process->run();

    $files = [
      'docroot/sites/default/local.drushrc.php',
      'legacy.aliases.drushrc.php',
      'drush/drushrc.php',
      'drush/site-aliases/legacy.aliases.drushrc.php',
      'drush/sites/.checksums',
      'example.acsf.aliases.yml',
      'example.local.aliases.yml',
      'tests/behat/local.yml',
    ];
    foreach ($files as $key => $file) {
      if (!file_exists($file)) {
        unset($files[$key]);
      }
    }
    $this->updater->getFileSystem()->chmod('docroot/sites/default', 0755);
    $this->updater->getFileSystem()->chmod($files, 0777);
    $this->updater->deleteFile($files);
    $this->updater->getFileSystem()->mirror('drush/site-aliases', 'drush/sites');
    $this->updater->getFileSystem()->remove('drush/site-aliases');

    $finder = new Finder();
    $finder->files()->in(['drush/sites'])->name('*.md5');
    $this->updater->getFileSystem()->remove(iterator_to_array($finder->getIterator()));
    $messages[] = "BLT attempted to upgrade your project-specific drush aliases. Please review and manually convert any that remain.";

    $this->updater->moveFile('blt/example.project.local.yml', 'blt/example.local.blt.yml', TRUE);
    $this->updater->moveFile('blt/project.local.yml', 'blt/local.blt.yml', TRUE);
    $this->updater->moveFile('blt/project.yml', 'blt/blt.yml', TRUE);
    $this->updater->moveFile('blt/ci.yml', 'blt/ci.blt.yml', TRUE);
    $messages[] = "BLT configuration files have been renamed.";

    $rekey_map = [
      'target-hooks.frontend-setup' => 'target-hooks.frontend-reqs',
      'target-hooks.frontend-build' => 'target-hooks.frontend-assets',
      'target-hooks' => 'command-hooks',
    ];

    $project_yml = $this->updater->getProjectYml();
    $project_config = new Data($project_yml);
    foreach ($rekey_map as $original => $new) {
      $value = $project_config->get($original);
      $project_config->set($new, $value);
      $project_config->remove($original);
    }
    $this->updater->writeProjectYml($project_yml);

    if (file_exists($this->updater->projectLocalYmlFilepath)) {
      $project_local_yml = $this->updater->getProjectLocalYml();
      unset($project_local_yml['drush']['default_alias']);
      unset($project_local_yml['drush']['aliases']['local']);
      $this->updater->writeProjectLocalYml($project_local_yml);;
    }

    $process = new Process("blt blt:init:settings", $this->updater->getRepoRoot());
    $process->run();

    $formattedBlock = $this->updater->getFormatter()->formatBlock($messages, 'ice');
    $this->updater->getOutput()->writeln("");
    $this->updater->getOutput()->writeln($formattedBlock);
    $this->updater->getOutput()->writeln("");
  }

  /**
   * 9.1.0-alpha1.
   *
   * @Update(
   *    version = "9001000",
   *    description = "Add deployment_identifier to project .gitignore and re-syncs ci.blt.yml."
   * )
   */
  public function update_9001000() {
    $this->updater->syncWithTemplate('.gitignore', TRUE);
    $messages = ['.gitignore has been updated. Review it for any custom changes that may have been overwritten.'];

    $formattedBlock = $this->updater->getFormatter()->formatBlock($messages, 'ice');
    $this->updater->getOutput()->writeln("");
    $this->updater->getOutput()->writeln($formattedBlock);
    $this->updater->getOutput()->writeln("");

    $this->updater->syncWithTemplate('blt/ci.blt.yml', TRUE);
    $messages = ['blt/ci.blt.yml has been updated. Review it for any custom changes that may have been overwritten.'];

    $formattedBlock = $this->updater->getFormatter()->formatBlock($messages, 'ice');
    $this->updater->getOutput()->writeln("");
    $this->updater->getOutput()->writeln($formattedBlock);
    $this->updater->getOutput()->writeln("");

    // Update composer.json to include new BLT required/suggested files.
    // Pulls in wikimedia/composer-merge-plugin and composer/installers settings.
    $project_composer_json = $this->updater->getRepoRoot() . '/composer.json';
    $template_composer_json = $this->updater->getBltRoot() . '/template/composer.json';
    $munged_json = ComposerMunge::mungeFiles($project_composer_json, $template_composer_json);
    $bytes = file_put_contents($project_composer_json, $munged_json);
    if (!$bytes) {
      $messages = ["Could not update $project_composer_json."];
    }
    else {
      $messages = ["Updated $project_composer_json. Review changes, then re-run composer update."];
    }

    $formattedBlock = $this->updater->getFormatter()->formatBlock($messages, 'ice');
    $this->updater->getOutput()->writeln("");
    $this->updater->getOutput()->writeln($formattedBlock);
    $this->updater->getOutput()->writeln("");
  }

  /**
   * 9.1.0.
   *
   * @Update(
   *    version = "9001001",
   *    description = "Adjust Drush 9 Composer contrib directory."
   * )
   */
  public function update_9001001() {
    $this->updater->syncWithTemplate('.gitignore', TRUE);
    $composer_json = $this->updater->getComposerJson();
    if (isset($composer_json['extra']['installer-paths']['drush/contrib/{$name}'])) {
      unset($composer_json['extra']['installer-paths']['drush/contrib/{$name}']);
    }
    $composer_json['extra']['installer-paths']['drush/Commands/{$name}'][] = 'type:drupal-drush';
    $this->updater->writeComposerJson($composer_json);
    $messages = [
      "Your composer.json file has been modified to be compatible with Drush 9.",
      "You must execute `composer update --lock` to update your lock file.",
    ];
    $formattedBlock = $this->updater->getFormatter()->formatBlock($messages, 'ice');
    $this->updater->getOutput()->writeln("");
    $this->updater->getOutput()->writeln($formattedBlock);
    $this->updater->getOutput()->writeln("");
  }

  /**
   * 9.2.0.
   *
   * @Update(
   *    version = "9002000",
   *    description = "Factory Hooks Drush 9 bug fixes and enhancements for db-update."
   * )
   */
  public function update_9002000() {
    if (file_exists($this->updater->getRepoRoot() . '/factory-hooks')) {
      $messages = [
        "This update will update the files in your existing factory hooks directory.",
        "Review the resulting files and ensure that any customizations have been re-added.",
      ];
      $this->updater->executeCommand("./vendor/bin/blt recipes:acsf:init:hooks");
    }
    $formattedBlock = $this->updater->getFormatter()->formatBlock($messages, 'ice');
    $this->updater->getOutput()->writeln("");
    $this->updater->getOutput()->writeln($formattedBlock);
    $this->updater->getOutput()->writeln("");
  }
}
