<?php

namespace Acquia\Blt\Robo\Commands\Uninstall;

use Acquia\Blt\Robo\BltTasks;
use function file_get_contents;
use Robo\Contract\VerbosityThresholdInterface;

/**
 * Defines commands in the "uninstall" namespace.
 */
class UninstallCommand extends BltTasks {
  /**
   * @var string*/
  protected $repoRoot;

  /**
   * @var \Symfony\Component\Filesystem\Filesystem*/
  protected $fs;

  /**
   * Uninstalls BLT.
   *
   * @aliases rdd uninstall
   *
   * @throws \Exception
   */
  public function uninstall() {
    $confirm = $this->confirm("This will completely remove BLT from your local. Continue?");
    if ($confirm) {

      // Provide guidance for explicitly requiring removed packages.
      // Guidance: which features must be replaced with custom solutions.
      // Remove blt dir
      // Remove files that integrate with BLT scripts:
      // rm -rf .acquia-pipelines.yml .travis.yml hooks factory-hooks .git/hooks
      // Remove references to composer.required.json
      // Remove references to composer.suggested.json.
      $this->taskFilesystemStack()
        ->remove([
          "blt",
          "readme",
          "hooks",
          "actory-hooks",
          "vendor",
          ".git/hooks",
          "acquia-pipelines.yml",
          "travis.yml",
          "composer.lock",
        ])
        ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
        ->run();

      // Remove relevant entries from .gitignore, like vendor.
      // Modify settings.php, remove blt references.
      $this->replaceInFile('docroot/sites/default/settings.php', 'require DRUPAL_ROOT . "/../vendor/acquia/blt/settings/blt.settings.php";', '');

      // Provide notice regarding removed integrations.
      // Create test for command.
      // Ensure it can be executed against an installed site.
      // Remove from Composer.
      $this->removeBLT();
    }
  }

  /**
   * Uninstalls Installs acquia/blt via Composer.
   *
   * @throws \Exception
   */
  protected function remove() {

    $this->say("Removing acquia/blt");
    $package_options = [
      'package_name' => 'acquia/blt',
    ];
    return $this->invokeCommand('internal:composer:remove', $package_options);
  }

  /**
   * Performs a find and replace in a text file.
   *
   * @param string $source
   *   The source filepath, relative to the repository root.
   * @param string $original
   *   The original string to find.
   * @param string $replacement
   *   The string with which to replace the original.
   */
  public function replaceInFile($source, $original, $replacement) {
    $source_path = $this->getRepoRoot() . '/' . $source;
    if ($this->getFileSystem()->exists($source)) {
      $contents = file_get_contents($source_path);
      $new_contents = str_replace($original, $replacement, $contents);
      file_put_contents($source_path, $new_contents);
    }
  }

  /**
   * Returns $this->repoRoot.
   *
   * @return string
   *   The filepath of the repository root directory.
   */
  public function getRepoRoot() {
    return $this->repoRoot;
  }

  /**
   * @return \Symfony\Component\Filesystem\Filesystem
   */
  public function getFileSystem() {
    return $this->fs;
  }

}
