<?php

namespace Acquia\Blt\Robo\Commands\Validate;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;

/**
 * Defines commands in the "validate:phpcs*" namespace.
 */
class PhpcsCommand extends BltTasks {

  /**
   * Executes PHP Code Sniffer against all phpcs.filesets files.
   *
   * By default, these include custom themes, modules, and tests.
   *
   * @command validate:phpcs
   */
  public function sniffFileSets() {
    $bin = $this->getConfigValue('composer.bin');
    $result = $this->taskExecStack()
      ->dir($this->getConfigValue('repo.root'))
      ->exec("$bin/phpcs")
      ->run();
    $exit_code = $result->getExitCode();
    if ($exit_code) {
      $this->logger->notice('Try running `blt fix:phpcbf` to automatically fix standards violations.');
      throw new BltException("PHPCS failed.");
    }
  }

  /**
   * Executes PHP Code Sniffer against a list of files, if in phpcs.filesets.
   *
   * This command will execute PHP Codesniffer against a list of files if those
   * files are a subset of the phpcs.filesets filesets.
   *
   * @command validate:phpcs:files
   *
   * @param string $file_list
   *   A list of files to scan, separated by \n.
   *
   * @return int
   */
  public function sniffFileList($file_list) {
    $this->say("Sniffing directories containing changed files...");
    $files = explode("\n", $file_list);
    $files = array_filter($files);

    // We must scan directories rather than individual files in order for PHPCS
    // extension constraints to be recognized.
    foreach ($files as $key => $file) {
      $files[$key] = dirname($file);
    }
    $files = array_unique($files);

    $exit_code = $this->doSniffFileList($files);

    return $exit_code;
  }

  /**
   * Executes PHP Code Sniffer against an array of files.
   *
   * @param array $files
   *   A flat array of absolute file paths.
   *
   * @return int
   */
  protected function doSniffFileList(array $files) {
    if ($files) {
      $temp_path = $this->getConfigValue('repo.root') . '/tmp/phpcs-fileset';
      $this->taskWriteToFile($temp_path)
        ->lines($files)
        ->run();

      $bin = $this->getConfigValue('composer.bin') . '/phpcs';
      $result = $this->taskExecStack()
        ->exec("'$bin' --file-list='$temp_path' -l")
        ->printMetadata(FALSE)
        ->run();

      unlink($temp_path);

      return $result->getExitCode();
    }

    return 0;
  }

}
