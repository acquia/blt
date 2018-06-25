<?php

namespace Acquia\Blt\Update;

use Acquia\Blt\Robo\Common\YamlMunge;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\IndexedReader;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 *
 */
class Updater {

  /**
   * @var \Doctrine\Common\Annotations\IndexedReader
   */
  protected $annotationsReader;

  /**
   * @var \Symfony\Component\Console\Output\ConsoleOutput*/
  protected $output;

  /**
   * @var \Symfony\Component\Console\Helper\FormatterHelper
   */
  protected $formatter;

  /**
   * @var string*/
  protected $repoRoot;

  /**
   * @var string
   */
  protected $bltRoot;

  /**
   * @var \Symfony\Component\Filesystem\Filesystem*/
  protected $fs;

  /**
   * @var string
   */
  protected $composerJsonFilepath;

  /**
   * @var string
   */
  protected $composerRequiredJsonFilepath;

  /**
   * @var string
   */
  protected $composerSuggestedJsonFilepath;

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
    $this->setBltRoot($repo_root . '/vendor/acquia/blt');
    $this->composerJsonFilepath = $this->repoRoot . '/composer.json';
    $this->composerRequiredJsonFilepath = $this->getBltRoot() . '/composer.required.json';
    $this->composerSuggestedJsonFilepath = $this->getBltRoot() . '/composer.suggested.json';
    $this->templateComposerJsonFilepath = $this->getBltRoot() . '/template/composer.json';
    $this->projectYmlFilepath = $this->repoRoot . '/blt/blt.yml';
    $this->projectLocalYmlFilepath = $this->repoRoot . '/blt/local.blt.yml';
    $this->formatter = new FormatterHelper();

    // Create "ice" style.
    $this->getOutput()->getFormatter()->setStyle('ice', new OutputFormatterStyle('white', 'blue'));
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
   * Sets $this->bltRoot.
   *
   * @param string $blt_root
   */
  public function setBltRoot($blt_root) {
    $this->bltRoot = $blt_root;
  }

  public function getBltRoot() {
    return $this->bltRoot;
  }

  /**
   * @return ConsoleOutput
   */
  public function getOutput() {
    return $this->output;
  }

  /**
   * @return \Symfony\Component\Console\Helper\FormatterHelper
   */
  public function getFormatter() {
    return $this->formatter;
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
   *   The starting version, e.g., 8005000.
   *
   * @param string $ending_version
   *   The ending version, e.g., 8005001.
   *
   * @return array
   *   An array of applicable update methods, keyed by method name. Each row
   *   contains the metadata from the Update annotation.
   */
  public function getUpdates($starting_version, $ending_version = NULL) {
    if (!$ending_version) {
      $ending_version = $this->getLatestUpdateMethodVersion();
    }

    $updates = [];
    $update_methods = $this->getAllUpdateMethods();

    /**
     * @var string $method_name
     * @var Update $metadata
     */
    foreach ($update_methods as $method_name => $metadata) {
      $version = $metadata->version;

      if (($version > $starting_version) && $version <= $ending_version) {
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
  public function getAllUpdateMethods() {
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
   * Gets the latest (highest numbered) update method.
   *
   * @return int mixed
   *   Returns the schema version for the latest update method.
   */
  public function getLatestUpdateMethodVersion() {
    $update_methods = $this->getAllUpdateMethods();
    $methods_by_number = [];
    foreach ($update_methods as $update_method) {
      $methods_by_number[$update_method->version] = $update_method;
    }

    $versions = array_keys($methods_by_number);
    $latest_version = max($versions);

    return $latest_version;
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
   *   The composer package name, e.g., 'drupal/features'.
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
   *   The key of the scripts to remove, e.g., post-create-project-cmd.
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
   * Returns composer.required.json content.
   *
   * @return array
   *   The contents of composer.required.json.
   */
  public function getComposerRequiredJson() {
    $composer_required_json = json_decode(file_get_contents($this->composerRequiredJsonFilepath), TRUE);

    return $composer_required_json;
  }

  /**
   * Returns composer.suggested.json content.
   *
   * @return array
   *   The contents of composer.suggested.json.
   */
  public function getComposerSuggestedJson() {
    $composer_suggested_json = json_decode(file_get_contents($this->composerSuggestedJsonFilepath), TRUE);

    return $composer_suggested_json;
  }

  /**
   * Returns template/composer.json content.
   *
   * @return array
   *   The contents of template/composer.json.
   */
  public function getTemplateComposerJson() {
    $template_composer_json = json_decode(file_get_contents($this->templateComposerJsonFilepath), TRUE);

    return $template_composer_json;
  }

  /**
   * Writes an array to composer.json.
   *
   * @param array $contents
   *   The new contents of composer.json.
   */
  public function writeComposerJson($contents) {
    // Ensure that require and require-dev are objects and not arrays.
    if (array_key_exists('require', $contents) && is_array($contents['require'])) {
      $contents['require'] = (object) $contents['require'];
    }
    if (array_key_exists('require-dev', $contents)&& is_array($contents['require-dev'])) {
      $contents['require-dev'] = (object) $contents['require-dev'];
    }
    file_put_contents($this->composerJsonFilepath, json_encode($contents, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
  }

  /**
   * @return mixed
   */
  public function getProjectYml() {
    return YamlMunge::parseFile($this->projectYmlFilepath);
  }

  /**
   * @return mixed
   */
  public function getProjectLocalYml() {
    return YamlMunge::parseFile($this->projectLocalYmlFilepath);
  }

  /**
   * @param $contents
   */
  public function writeProjectYml($contents) {
    YamlMunge::writeFile($this->projectYmlFilepath, $contents);
  }

  /**
   * @param $contents
   */
  public function writeProjectLocalYml($contents) {
    YamlMunge::writeFile($this->projectLocalYmlFilepath, $contents);
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
   * Copies a file from the BLT template to the repository.
   *
   * @param string $filePath
   *   The filepath, relative to the BLT template directory.
   * @param bool $overwrite
   *   If true, target files newer than origin files are overwritten.
   */
  public function syncWithTemplate($filePath, $overwrite = FALSE) {
    $sourcePath = $this->getBltRoot() . '/template/' . $filePath;
    $targetPath = $this->getRepoRoot() . '/' . $filePath;

    if ($this->getFileSystem()->exists($sourcePath)) {
      try {
        $this->getFileSystem()->copy($sourcePath, $targetPath, $overwrite);
      }
      catch (IOException $e) {
        throw $e;
      }
    }

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
   * @param string|array $filepaths
   */
  public function deleteFile($filepaths) {
    $filepaths = (array) $filepaths;
    $files = [];
    foreach ($filepaths as $filepath) {
      $files[] = $this->getRepoRoot() . '/' . $filepath;
    }
    $this->getFileSystem()->remove($files);
  }

}
