<?php

namespace Acquia\Blt\Robo\Commands\Validate;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;

/**
 * Defines commands in the "tests:phpstan:sniff:all*" namespace.
 */
class PhpstanCommand extends BltTasks {

  /**
   * Executes PHPStan: Static Analysis Tool against all phpstan.filesets files.
   *
   * By default, these include custom themes, modules, and tests.
   *
   * @command tests:phpstan:sniff:all
   *
   * @aliases tptsa phpstan tests:phpstan:sniff validate:phpstan
   */
  public function sniffFileSets() {
    /** @var \Acquia\Blt\Robo\Filesets\FilesetManager $fileset_manager */
    $fileset_manager = $this->getContainer()->get('filesetManager');
    $fileset_ids = $this->getConfigValue('validate.phpstan.filesets');
    $filesets = $fileset_manager->getFilesets($fileset_ids);
    $bin = $this->getConfigValue('composer.bin');
    $repo_root = $this->getConfigValue('repo.root');
    $stringOfFiles = $this->fileSetToList($filesets);

    $command = "$bin/phpstan analyse $stringOfFiles --configuration=$repo_root/phpstan.neon";

    $result = $this->taskExecStack()
      ->dir($this->getConfigValue('repo.root'))
      ->exec($command)
      ->run();
    $exit_code = $result->getExitCode();

    if ($exit_code) {
      throw new BltException('PHPStan Failed.  To disable PHPStan scans, set disable-targets.tests.phpstan.sniff.files to true in blt.yml.');
    }
  }

  /**
   * Executes PHPStan against a list of files, if in phpstan.filesets.
   *
   * This command will execute PHPStan against a list of files if those
   * files are a subset of the phpstan.filesets filesets.
   *
   * @command tests:phpstan:sniff:files
   * @aliases tptsf
   *
   * @param string $file_list
   *   A list of files to scan, separated by \n.
   *
   * @return int
   */
  public function sniffFileList($file_list) {
    $repo_root = $this->getConfigValue('repo.root');

    $this->say("Sniffing directories containing changed files...");
    $files = explode(" ", $file_list);
    if ($files) {
      $command = "analyse $file_list --configuration=$repo_root/phpstan.neon";
      $exit_code = $this->doSniff($command);

      return $exit_code;
    }

    return 0;
  }

  /**
   * Executes PHPStan against (unstaged) modified or untracked files in repo.
   *
   * This command will execute PHPStan against modified/untracked files
   * if those files are a subset of the phpstan.filesets filesets.
   *
   * @command tests:phpstan:sniff:modified
   * @aliases tpsm
   *
   * @return int
   */
  public function sniffModified() {
    $this->say("Sniffing modified and untracked files in repo...");
    $arguments = "--filter=gitmodified " . $this->getConfigValue('repo.root');
    $exit_code = $this->doSniff($arguments);

    return $exit_code;
  }

  /**
   * Executes PHPStan using specified options/arguments.
   *
   * @param string $arguments
   *   The command arguments/options.
   *
   * @return int
   */
  protected function doSniff($arguments) {
    $bin = $this->getConfigValue('composer.bin') . '/phpstan';
    $command = "'$bin' $arguments";
    if ($this->output()->isVerbose()) {
      $command .= ' -v';
    }
    elseif ($this->output()->isVeryVerbose()) {
      $command .= ' -vv';
    }
    $result = $this->taskExecStack()
      ->exec($command)
      ->printMetadata(FALSE)
      ->run();

    return $result->getExitCode();
  }

  /**
   * Converts fileset to a string of all the files.
   *
   * @param \Symfony\Component\Finder\Finder[] $filesets
   *
   * @return string | NULL
   *   A space delimeted string of files.
   */
  protected function fileSetToList(array $filesets) {
    $stringOfFiles = '';
    foreach ($filesets as $fileset_id => $fileset) {
      if (is_null($fileset) || iterator_count($fileset) == 0) {
        $this->logger->info("No files were found in fileset $fileset_id. Skipped.");
        continue;
      }

      $files = iterator_to_array($fileset);
      foreach ($files as $filesname => $file) {
        $stringOfFiles = $stringOfFiles . "$filesname ";
      }
    }

    return $stringOfFiles;
  }

}
