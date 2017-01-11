<?php

namespace Acquia\Blt\Update;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\IndexedReader;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;
use vierbergenlars\SemVer\version;

/**
 *
 */
class Updater {

  /**
   * @var \Symfony\Component\Console\Output\ConsoleOutput*/
  protected $output;

  /**
   * @var string*/
  protected $repoRoot;

  /**
   * @var \Symfony\Component\Filesystem\Filesystem*/
  protected $fs;

  /**
   * @var string
   */
  protected $composerJsonFilepath;

  /**
   * Updater constructor.
   *
   * @param string $update_class
   *   The name of the class containing the update methods to be executed.
   * @param string $repo_root
   *   The root directory for this project.
   */
  public function __construct($update_class, $repo_root) {
    $this->output = new ConsoleOutput();
    $this->output->setFormatter(new OutputFormatter(TRUE));
    AnnotationRegistry::registerFile(__DIR__ . '/../Annotations/Update.php');
    $this->annotationsReader = new IndexedReader(new AnnotationReader());
    $this->updateClassName = $update_class;
    $this->fs = new Filesystem();
    $this->setRepoRoot($repo_root);
    $this->composerJsonFilepath = $this->repoRoot . '/composer.json';
    $this->projectYmlFilepath = $this->repoRoot . '/blt/project.yml';
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
   * The filepath of the repository root directory.
   *
   * This directory is expected to contain the composer.json that defines
   * acquia/blt as a dependency.
   *
   * @param string $repoRoot
   *   The filepath of the repository root directory.
   */
  public function setRepoRoot($repoRoot) {
    if (!$this->fs->exists($repoRoot)) {
      throw new FileNotFoundException();
    }

    $this->repoRoot = $repoRoot;
  }

  /**
   * @return ConsoleOutput
   */
  public function getOutput() {
    return $this->output;
  }

  /**
   * @return \Symfony\Component\Filesystem\Filesystem
   */
  public function getFileSystem() {
    return $this->fs;
  }

  /**
   * Executes an array of updates.
   *
   * @param $updates \Acquia\Blt\Annotations\Update[]
   */
  public function executeUpdates($updates) {
    /** @var Updates $updates_object */
    $updates_object = new $this->updateClassName($this);
    $this->output->writeln("Executing updates...");

    /**
     * @var string $method_name
     * @var Update $update
     */
    foreach ($updates as $method_name => $update) {
      $this->output->writeln("-> $method_name: {$update->description}");
      call_user_func([$updates_object, $method_name]);
    }
  }

  /**
   * Prints a human-readable list of update methods to the screen.
   *
   * @param $updates \Acquia\Blt\Annotations\Update[]
   */
  public function printUpdates($updates) {
    /**
     * @var string $method_name
     * @var Update $update
     */
    foreach ($updates as $method_name => $update) {
      $this->output->writeln(" - $method_name: {$update->description}");
    }
    $this->output->writeln('');
  }

  /**
   * Gets all applicable updates for a given version delta.
   *
   * @param string $starting_version
   *   The starting version. E.g., 8.5.0.
   *
   * @param string $ending_version
   *   The ending version. E.g., 8.5.1.
   *
   * @return array
   *   An array of applicable update methods, keyed by method name. Each row
   *   contains the metadata from the Update annotation.
   */
  public function getUpdates($starting_version, $ending_version) {
    $updates = [];
    $update_methods = $this->getAllUpdateMethods();
    $include_all_updates = FALSE;

    if (strpos($starting_version, 'dev') !== FALSE
      || strpos($ending_version, 'dev') !== FALSE) {
      $this->output->writeln("<comment>You are (or were) using a development branch of BLT. It is assumed that you require all scripted updates.</comment>");
      $include_all_updates = TRUE;
    }

    /**
     * @var string $method_name
     * @var Update $metadata
     */
    foreach ($update_methods as $method_name => $metadata) {
      $version = $metadata->version;

      if ($include_all_updates
        || (version::gt($version, $starting_version) && version::lte($version, $ending_version))) {
        $updates[$method_name] = $metadata;
      }
    }

    return $updates;
  }

  /**
   * Gather an array of all available update methods.
   *
   * This will only return methods using the Update annotation.
   *
   * @see drupal_get_schema_versions()
   */
  protected function getAllUpdateMethods() {
    $update_methods = [];
    $methods = get_class_methods($this->updateClassName);
    foreach ($methods as $method_name) {
      $reflectionMethod = new \ReflectionMethod($this->updateClassName, $method_name);
      $annotations = $this->annotationsReader->getMethodAnnotation($reflectionMethod, 'Acquia\Blt\Annotations\Update');
      if ($annotations) {
        $update_methods[$method_name] = $annotations;
      }
    }

    return $update_methods;
  }

  /**
   * @param string $command
   * @param string $cwd
   * @param bool $display_output
   *   Optional. Whether to print command output to screen. Changes return
   *   value.
   * @param bool $mustRun
   *
   * @return bool|string
   *   If $display_output is true, method will return TRUE for success, FALSE
   *   for failure. If $display_output false, method will return command output.
   */
  public static function executeCommand($command, $cwd = NULL, $display_output = TRUE, $mustRun = TRUE) {
    $timeout = 10800;
    $env = [
      'COMPOSER_PROCESS_TIMEOUT' => $timeout,
    ] + $_ENV;
    $process = new Process($command, $cwd, $env, NULL, $timeout);
    $method = $mustRun ? 'mustRun' : 'run';
    if ($display_output) {
      $process->$method(function ($type, $buffer) {
        print $buffer;
      });
      return $process->isSuccessful();
    }
    else {
      $process->$method();
      return $process->getOutput();
    }
  }

  /**
   * Removes a patch from repo's root composer.json file.
   *
   * @param string $package
   *   The composer package name. E.g., 'drupal/features'.
   *
   * @param string $url
   *   The URL of the patch.
   *
   * @return bool
   *   TRUE if patch was removed, otherwise FALSE.
   */
  public function removeComposerPatch($package, $url) {
    $composer_json = $this->getComposerJson();
    if (!empty($composer_json['extra']['patches'][$package])) {
      foreach ($composer_json['extra']['patches'][$package] as $key => $patch_url) {
        if ($patch_url == $url) {
          unset($composer_json['extra']['patches'][$package][$key]);
          // If that was the only patch for this module, unset the parent too.
          if (empty($composer_json['extra']['patches'][$package])) {
            unset($composer_json['extra']['patches'][$package]);
          }
          $this->writeComposerJson($composer_json);

          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
   * Removes a repository from composer.json using the repository url[.
   *
   * @param string $repo_url
   *   The url property of the repository to remove.
   *
   * @return bool
   *   TRUE if repo was removed, otherwise false.
   */
  public function removeComposerRepository($repo_url) {
    $composer_json = $this->getComposerJson();
    if (!empty($composer_json['repositories'])) {
      foreach ($composer_json['repositories'] as $key => $repo) {
        $url = $repo['url'];
        if ($repo_url == $url) {
          unset($composer_json['repositories'][$key]);
          $this->writeComposerJson($composer_json);

          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
   * Removes a repository from composer.json using the repository url[.
   *
   * @param string $script_key
   *   The key of the scripts to remove. E.g., post-create-project-cmd.
   *
   * @return bool
   *   TRUE if script was removed, otherwise false.
   */
  public function removeComposerScript($script_key) {
    $composer_json = $this->getComposerJson();
    if (!empty($composer_json['scripts'])) {
      foreach ($composer_json['scripts'] as $key => $script) {
        if ($script_key == $key) {
          unset($composer_json['scripts'][$key]);
          $this->writeComposerJson($composer_json);

          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
   * Returns composer.json content.
   *
   * @return array
   *   The contents of composer.json.
   */
  public function getComposerJson() {
    $composer_json = json_decode(file_get_contents($this->composerJsonFilepath), TRUE);

    return $composer_json;
  }

  /**
   * Writes an array to composer.json.
   *
   * @param array $contents
   *   The new contents of composer.json.
   */
  public function writeComposerJson($contents) {
    file_put_contents($this->composerJsonFilepath, json_encode($contents, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
  }

  /**
   * @return mixed
   */
  public function getProjectConfig() {
    $project_yml = Yaml::parse(file_get_contents($this->projectYmlFilepath));

    return $project_yml;
  }

  /**
   * @param $contents
   */
  public function writeProjectConfig($contents) {
    file_put_contents($this->projectYmlFilepath, Yaml::dump($contents, 3, 2));
  }

  /**
   * Moves a file from one location to another, relative to repo root.
   *
   * @param string $source
   *   The source filepath, relative to the repository root.
   * @param string $target
   *   The target filepath, relative to the repository root.
   *
   * @return bool
   *   FALSE if nothing happened.
   */
  public function moveFile($source, $target, $overwrite = FALSE) {
    $source_path = $this->getRepoRoot() . '/' . $source;
    $target_path = $this->getRepoRoot() . '/' . $target;

    if ($this->getFileSystem()->exists($source)) {
      if ($overwrite) {
        $this->getFileSystem()->rename($source_path, $target_path, TRUE);
      }
      // We "fail" silently if target file already exists. The default behavior
      // is quiet and non-destructive.
      elseif (!$this->getFileSystem()->exists($target_path)) {
        $this->getFileSystem()->rename($source_path, $target_path);
      }
    }

    return FALSE;
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
   * @param $filepath
   */
  public function deleteFile($filepath) {
    $abs_path = $this->getRepoRoot() . '/' . $filepath;
    $this->getFileSystem()->remove($abs_path);
  }

}
