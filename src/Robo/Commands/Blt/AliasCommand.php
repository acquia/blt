<?php

namespace Acquia\Blt\Robo\Commands\Blt;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Common\ComposerMunge;
use Acquia\Blt\Robo\Common\YamlMunge;
use Acquia\Blt\Update\Updater;
use Robo\Contract\VerbosityThresholdInterface;
use Symfony\Component\Filesystem\Filesystem;
use Tivie\OS\Detector;
use const Tivie\OS\MACOSX;

/**
 * Defines commands for installing and updating the BLT alias.
 */
class AliasCommand extends BltTasks {

  /**
   * Installs the BLT alias for command line usage.
   *
   * @command install-alias
   */
  public function installBltAlias() {
    $this->createOsxBashProfile();
    if (!$this->getInspector()->isBltAliasInstalled()) {
      $this->say("BLT can automatically create a Bash alias to make it easier to run BLT tasks.");
      $this->say("This alias may be created in <comment>.bash_profile</comment> or <comment>.bashrc</comment> depending on your system architecture.");

      $create = $this->confirm("Install alias?");
      if ($create) {
        $this->say("Installing <comment>blt</comment> alias...");
        // @todo replace this with PHP logic.
        exec($this->getConfigValue('blt.root') . '/scripts/blt/install-alias.sh -y');
      }
      else {
        $this->say("The <comment>blt</comment> alias was not installed.");
      }
    }
    elseif (!$this->isBltAliasUpToDate()) {
      $this->logger->warning("Your BLT alias is out of date. ");
      $confirm = $this->confirm("Would you like to update it?");
      if ($confirm) {
        $this->updateAlias();
      }
    }
  }

  /**
   * Checks if the installed alias is up-to-date.
   *
   * @return bool
   *   TRUE if the installed alias is up to date.
   */
  protected function isBltAliasUpToDate() {
    $installed_alias = $this->getInstalledAlias();
    $canonical_alias = file_get_contents($this->getConfigValue('repo.root') . '/scripts/blt/alias');

    return $installed_alias['alias'] === $canonical_alias;
  }

  /**
   * Gets information about the installed BLT alias.
   *
   * @return array
   *   An array of information about the installed BLT alias.
   */
  protected function getInstalledAlias() {
    $alias_length = NULL;
    $alias = NULL;
    $config_file = $this->getInspector()->getCliConfigFile();
    $contents = file_get_contents($config_file);
    $begin_alias_pos = strpos($contents, 'function blt() {');
    $end_alias_pos = $this->getClosingBracketPosition($contents, $begin_alias_pos);

    if (!is_null($end_alias_pos)) {
      $alias_length = $end_alias_pos - $begin_alias_pos;
      $alias = substr($contents, $begin_alias_pos, $alias_length);
    }

    return [
      'config_file' => $config_file,
      'contents' => $contents,
      'start_pos' => $begin_alias_pos,
      'end_pos' => $end_alias_pos,
      'length' => $alias_length,
      'alias' => $alias,
    ];
  }

  /**
   * Replaces installed alias with up-to-date alias.
   */
  protected function updateAlias() {
    $installed_alias = $this->getInstalledAlias();
    $canonical_alias = file_get_contents($this->getConfigValue('repo.root') . '/scripts/blt/alias');
    substr_replace($installed_alias['config_file'], $canonical_alias, $installed_alias['start_pos'],
      $installed_alias['length']);
  }

  /**
   * Find the position of a closing bracket for a given stanza in a string.
   *
   * @param $contents
   *   The string containing the brackets.
   * @param int $start_pos
   *   The position of the opening bracket in the string that should be matched.
   *
   * @return int|NULL
   *
   */
  protected function getClosingBracketPosition($contents, $start_pos) {
    $brackets = ['{'];
    for ($pos = $start_pos; $pos < strlen($contents); $pos++) {
      $char = substr($contents, $pos, 1);
      if ($char == '{') {
        array_push($brackets, $char);
      }
      elseif ($char == '}') {
        array_pop($brackets);
      }
      if (count($brackets) == 0) {
        return $pos;
      }
    }

    return NULL;
  }

  /**
   * Creates a ~/.bash_profile on OSX if one does not exist.
   */
  protected function createOsxBashProfile() {
    $os_detector = new Detector();
    $os_type = $os_detector->getType();
    if ($os_type == MACOSX) {
      $user = posix_getpwuid(posix_getuid());
      $home_dir = $user['dir'];
      if (!file_exists($home_dir . '/.bash_profile')) {
        $this->taskFilesystemStack()
          ->touch($home_dir . '/.bash_profile')
          ->run();
      }
    }
  }

}
