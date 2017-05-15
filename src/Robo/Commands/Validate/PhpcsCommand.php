<?php

namespace Acquia\Blt\Robo\Commands\Validate;

use Acquia\Blt\Robo\BltTasks;
use Robo\Contract\VerbosityThresholdInterface;
use Symfony\Component\Finder\Finder;

/**
 * Defines commands in the "validate:phpcs*" namespace.
 */
class PhpcsCommand extends BltTasks {

  protected $standard;

  /**
   * This hook will fire for all commands in this command file.
   *
   * @hook init
   */
  public function initialize() {
    $this->standard = $this->getConfigValue('repo.root') . '/vendor/drupal/coder/coder_sniffer/Drupal/ruleset.xml';
  }

  /**
   * Executes PHP Code Sniffer against all phpcs.filesets files.
   *
   * By default, these include custom themes, modules, and tests.
   *
   * @command validate:phpcs
   */
  public function phpcs() {
    $filesets_to_sniff = $this->getConfigValue('phpcs.filesets');
    $bin = $this->getConfigValue('composer.bin');
    $command = "'$bin/phpcs' --standard='{$this->standard}' '%s'";

    // @todo Compare the performance of this vs. dumping $files to a temp file
    // and executing phpcs --file-set=[tmp-file]. Also, compare vs. using
    // parallel processes.
    $result = $this->executeCommandAgainstFilesets($filesets_to_sniff, $command);

    return $result;
  }

  /**
   * Executes PHP Code Sniffer against a list of files, if in phpcs.filesets.
   *
   * This command will execute PHP Codesniffer against a list of files if those
   * files are a subset of the phpcs.filesets filesets.
   *
   * @command validate:phpcs:files
   *
   * @param string $file_list A list of files to scan, separated by \n.
   *
   * @return \Robo\Result
   *   The result of the PHPCS execution.
   */
  public function phpcsFiles($file_list) {
    $files = explode("\n", $file_list);
    $filesets_to_sniff = $this->getConfigValue('phpcs.filesets');
    // @todo do this for all filesets.
    $files_in_fileset = $this->filterFilesByFileset($files, $filesets_to_sniff[1]);
    $temp_path = $this->getConfigValue('repo.root') . '/tmp/phpcs-fileset';
    $this->taskWriteToFile($temp_path)
      ->lines($files_in_fileset)
      ->run();

    $bin = $this->getConfigValue('composer.bin') . '/phpcs';
    $result = $this->taskExecStack()
      ->exec("'$bin' --file-list='$temp_path' --standard='{$this->standard}'")
      ->run();

    unlink($temp_path);

    return $result;
  }

  /**
   * Returns the intersection of $files and a given fileset.
   *
   * @param array $files
   *   An array of absolute file paths.
   * @param string $fileset_id
   *   The ID for a given fileset.
   *
   * @return array
   *   The intersection of $files and the fileset.
   */
  protected function filterFilesByFileset($files, $fileset_id) {
    $absolute_files = array_map(array($this, 'prependRepoRoot'), $files);

    /** @var \Acquia\Blt\Robo\Filesets\FilesetManager $filesetManager */
    $filesetManager = $this->container->get('filesetManager');
    $fileset = $filesetManager->getFileset($fileset_id);

    // @todo Compare performance of this vs. using
    // array_intersect($files, array_keys(iterator_to_array($fileset)));
    $filter = function (\SplFileInfo $file) use ($absolute_files) {
      if (!in_array($file->getRealPath(), $absolute_files)) {
        return FALSE;
      }
    };
    $fileset->filter($filter);

    $files = iterator_to_array($fileset);
    $file_paths = array_keys($files);

    return $file_paths;
  }

  /**
   * Prepends the repo.root variable to a given filepath.
   *
   * @param string $relative_path
   *   A file path relative to repo.root.
   *
   * @return string
   *   The absolute file path.
   */
  protected function prependRepoRoot($relative_path) {
    $absolute_path = $this->getConfigValue('repo.root') . '/' . $relative_path;

    return $absolute_path;
  }

}
