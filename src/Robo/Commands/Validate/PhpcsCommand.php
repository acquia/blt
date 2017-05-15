<?php

namespace Acquia\Blt\Robo\Commands\Validate;

use Acquia\Blt\Robo\BltTasks;

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
    $fileset_ids = $this->getConfigValue('phpcs.filesets');
    $filesets = $this->getContainer()->get('filesetManager')->getFilesets($fileset_ids);
    $bin = $this->getConfigValue('composer.bin');
    $command = "'$bin/phpcs' --standard='{$this->standard}' '%s'";

    // @todo Compare the performance of this vs. dumping $files to a temp file
    // and executing phpcs --file-set=[tmp-file]. Also, compare vs. using
    // parallel processes.
    $result = $this->executeCommandAgainstFilesets($filesets, $command);

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
   * @param string $file_list
   *   A list of files to scan, separated by \n.
   *
   * @return int
   */
  public function phpcsFiles($file_list) {
    $this->say("Sniffing files...");

    $result = 0;
    /** @var \Acquia\Blt\Robo\Filesets\FilesetManager $fileset_manager */
    $fileset_manager = $this->getContainer()->get('filesetManager');
    $files = explode("\n", $file_list);
    $filesets_ids = $this->getConfigValue('phpcs.filesets');

    foreach ($filesets_ids as $fileset_id) {
      $fileset = $fileset_manager->getFileset($fileset_id);
      if (!is_null($fileset)) {
        $filtered_fileset = $fileset_manager->filterFilesByFileset($files, $fileset);
        $filtered_fileset = iterator_to_array($filtered_fileset);
        $files_in_fileset = array_keys($filtered_fileset);
        $result = $this->phpcsFileList($files_in_fileset);
        if ($result) {
          return $result;
        }
      }
    }

    return $result;
  }

  protected function phpcsFileList($file_list) {
    if ($file_list) {
      $temp_path = $this->getConfigValue('repo.root') . '/tmp/phpcs-fileset';
      $this->taskWriteToFile($temp_path)
        ->lines($file_list)
        ->run();

      $bin = $this->getConfigValue('composer.bin') . '/phpcs';
      $result = $this->taskExecStack()
        ->exec("'$bin' --file-list='$temp_path' --standard='{$this->standard}'")
        ->run();

      unlink($temp_path);

      return $result->getExitCode();
    }

    return 0;
  }

}
