<?php

namespace Acquia\Blt\Update;

use Acquia\Blt\Robo\Common\ComposerJson;
use Acquia\Blt\Robo\Common\YamlMunge;
use Acquia\Blt\Robo\Exceptions\BltException;
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
 * Helper class to run BLT updates.
 */
class Updater {

  /**
   * Annotations reader.
   *
   * @var \Doctrine\Common\Annotations\IndexedReader
   */
  protected $annotationsReader;

  /**
   * Console output.
   *
   * @var \Symfony\Component\Console\Output\ConsoleOutput*/
  protected $output;

  /**
   * Formatter helper.
   *
   * @var \Symfony\Component\Console\Helper\FormatterHelper
   */
  protected $formatter;

  /**
   * Repo root.
   *
   * @var string*/
  protected $repoRoot;

  /**
   * BLT root.
   *
   * @var string
   */
  protected $bltRoot;

  /**
   * Filesystem.
   *
   * @var \Symfony\Component\Filesystem\Filesystem*/
  protected $fs;

  /**
   * Cloud hooks status.
   *
   * @var bool
   */
  protected $cloudHooksAlreadyUpdated = FALSE;

  /**
   * Composer.json file.
   *
   * @var \Acquia\Blt\Robo\Common\ComposerJson
   */
  public $composerJson;

  /**
   * Composer.required.json file.
   *
   * @var \Acquia\Blt\Robo\Common\ComposerJson
   */
  public $composerRequiredJson;

  /**
   * Composer.suggested.json file.
   *
   * @var \Acquia\Blt\Robo\Common\ComposerJson
   */
  protected $composerSuggestedJson;

  /**
   * Template composer.json file.
   *
   * @var \Acquia\Blt\Robo\Common\ComposerJson
   */
  protected $templateComposerJson;

  /**
   * The name of the class containing the update methods to be executed.
   *
   * @var string
   */
  private $updateClassName;

  /**
   * Blt.yml filepath.
   *
   * @var string
   */
  public $projectYmlFilepath;

  /**
   * Local.blt.yml filepath.
   *
   * @var string
   */
  public $projectLocalYmlFilepath;

  /**
   * Updater constructor.
   *
   * @param string $update_class
   *   The name of the class containing the update methods to be executed.
   * @param string $repo_root
   *   The root directory for this project.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   * @throws \Doctrine\Common\Annotations\AnnotationException
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
    $this->composerJson = new ComposerJson($this->repoRoot);
    $this->templateComposerJson = new ComposerJson($this->getBltRoot() . '/subtree-splits/blt-project');
    try {
      $this->composerRequiredJson = new ComposerJson($this->getBltRoot(), 'composer.required.json');
      $this->composerSuggestedJson = new ComposerJson($this->getBltRoot(), 'composer.suggested.json');
    }
    catch (BltException $e) {
      // Must be a newer version of BLT, that's fine.
    }
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
   *   BLT root.
   */
  public function setBltRoot($blt_root) {
    $this->bltRoot = $blt_root;
  }

  /**
   * Get BLT root.
   */
  public function getBltRoot() {
    return $this->bltRoot;
  }

  /**
   * Gets output.
   *
   * @return \Symfony\Component\Console\Output\ConsoleOutput
   *   Console output.
   */
  public function getOutput() {
    return $this->output;
  }

  /**
   * Gets formatter.
   *
   * @return \Symfony\Component\Console\Helper\FormatterHelper
   *   Formatter helper.
   */
  public function getFormatter() {
    return $this->formatter;
  }

  /**
   * Gets filesystem.
   *
   * @return \Symfony\Component\Filesystem\Filesystem
   *   Filesystem.
   */
  public function getFileSystem() {
    return $this->fs;
  }

  /**
   * Executes an array of updates.
   *
   * @param \Acquia\Blt\Annotations\Update[] $updates
   *   List of updates.
   */
  public function executeUpdates(array $updates) {
    /** @var Updates $updates_object */
    $updates_object = new $this->updateClassName($this);
    $this->output->writeln("Executing updates...");

    foreach ($updates as $method_name => $update) {
      $this->output->writeln("-> $method_name: {$update->description}");
      call_user_func([$updates_object, $method_name]);
    }
  }

  /**
   * Prints a human-readable list of update methods to the screen.
   *
   * @param \Acquia\Blt\Annotations\Update[] $updates
   *   List of updates.
   */
  public function printUpdates(array $updates) {
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
   * @param string $ending_version
   *   The ending version, e.g., 8005001.
   *
   * @return array
   *   An array of applicable update methods, keyed by method name. Each row
   *   contains the metadata from the Update annotation.
   *
   * @throws \ReflectionException
   */
  public function getUpdates($starting_version, $ending_version = NULL) {
    if (!$ending_version) {
      $ending_version = $this->getLatestUpdateMethodVersion();
    }

    $updates = [];
    $update_methods = $this->getAllUpdateMethods();

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
   * @throws \ReflectionException
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
   * @return int|mixed
   *   Returns the schema version for the latest update method.
   *
   * @throws \ReflectionException
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
   * Runs a command.
   *
   * @param string $command
   *   Command.
   * @param string $cwd
   *   Working directory.
   * @param bool $display_output
   *   Optional. Whether to print command output to screen. Changes return
   *   value.
   * @param bool $mustRun
   *   Must run.
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
   * @param string $url
   *   The URL of the patch.
   *
   * @return bool
   *   TRUE if patch was removed, otherwise FALSE.
   */
  public function removeComposerPatch($package, $url) {
    if (!empty($this->composerJson->contents['extra']['patches'][$package])) {
      foreach ($this->composerJson->contents['extra']['patches'][$package] as $key => $patch_url) {
        if ($patch_url == $url) {
          unset($this->composerJson->contents['extra']['patches'][$package][$key]);
          // If that was the only patch for this module, unset the parent too.
          if (empty($this->composerJson->contents['extra']['patches'][$package])) {
            unset($this->composerJson->contents['extra']['patches'][$package]);
          }
          $this->composerJson->write();
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
    if (!empty($this->composerJson->contents['repositories'])) {
      foreach ($this->composerJson->contents['repositories'] as $key => $repo) {
        $url = $repo['url'];
        if ($repo_url == $url) {
          unset($this->composerJson->contents['repositories'][$key]);
          $this->composerJson->write();
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
    if (!empty($this->composerJson->contents['scripts'])) {
      foreach ($this->composerJson->contents['scripts'] as $key => $script) {
        if ($script_key == $key) {
          unset($this->composerJson->contents['scripts'][$key]);
          $this->composerJson->write();
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
   * Returns composer.json content.
   *
   * Deprecated. Use public ComposerJson members instead.
   *
   * @return array
   *   The contents of composer.json.
   */
  public function getComposerJson() {
    return $this->composerJson->contents;
  }

  /**
   * Returns composer.required.json content.
   *
   * Deprecated. Use public ComposerJson members instead.
   *
   * @return array
   *   The contents of composer.required.json.
   */
  public function getComposerRequiredJson() {
    return $this->composerRequiredJson->contents;
  }

  /**
   * Returns composer.suggested.json content.
   *
   * Deprecated. Use public ComposerJson members instead.
   *
   * @return array
   *   The contents of composer.suggested.json.
   */
  public function getComposerSuggestedJson() {
    return $this->composerSuggestedJson->contents;
  }

  /**
   * Returns template/composer.json content.
   *
   * Deprecated. Use public ComposerJson members instead.
   *
   * @return array
   *   The contents of template/composer.json.
   */
  public function getTemplateComposerJson() {
    return $this->templateComposerJson->contents;
  }

  /**
   * Writes an array to composer.json.
   *
   * Deprecated. Use public ComposerJson members instead.
   *
   * @param array $contents
   *   The new contents of composer.json.
   */
  public function writeComposerJson(array $contents) {
    $this->composerJson->contents = $contents;
    $this->composerJson->write();
  }

  /**
   * Get project yml.
   *
   * @return mixed
   *   Project YAML.
   */
  public function getProjectYml() {
    return YamlMunge::parseFile($this->projectYmlFilepath);
  }

  /**
   * Get project local yml.
   *
   * @return mixed
   *   Project YAML.
   */
  public function getProjectLocalYml() {
    return YamlMunge::parseFile($this->projectLocalYmlFilepath);
  }

  /**
   * Write project yml.
   *
   * @param array $contents
   *   YAML contents.
   */
  public function writeProjectYml(array $contents) {
    YamlMunge::writeFile($this->projectYmlFilepath, $contents);
  }

  /**
   * Write project local yml.
   *
   * @param array $contents
   *   YAML contents.
   */
  public function writeProjectLocalYml(array $contents) {
    YamlMunge::writeFile($this->projectLocalYmlFilepath, $contents);
  }

  /**
   * Moves a file from one location to another, relative to repo root.
   *
   * @param string $source
   *   The source filepath, relative to the repository root.
   * @param string $target
   *   The target filepath, relative to the repository root.
   * @param bool $overwrite
   *   Whether to overwrite.
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
    $sourcePath = $this->getBltRoot() . '/subtree-splits/blt-project/' . $filePath;
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
   * Delete file.
   *
   * @param string|array $filepaths
   *   Filepaths to delete.
   */
  public function deleteFile($filepaths) {
    $filepaths = (array) $filepaths;
    $files = [];
    foreach ($filepaths as $filepath) {
      $files[] = $this->getRepoRoot() . '/' . $filepath;
    }
    $this->getFileSystem()->remove($files);
  }

  /**
   * Regenerate Cloud Hooks, but only once.
   */
  public function regenerateCloudHooks() {
    if (file_exists($this->getRepoRoot() . '/hooks') && !$this->cloudHooksAlreadyUpdated) {
      self::executeCommand("./vendor/bin/blt recipes:cloud-hooks:init", NULL, FALSE);
      $this->cloudHooksAlreadyUpdated = TRUE;
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Regenerate Pipelines config if it exists.
   */
  public function regeneratePipelines() {
    if (file_exists($this->getRepoRoot() . '/acquia-pipelines.yml')) {
      self::executeCommand("./vendor/bin/blt recipes:ci:pipelines:init", NULL, FALSE);
      $this->getOutput()->writeln("acquia-pipelines.yml has been regenerated. Review the resulting file and re-add any customizations.");
    }
  }

}
