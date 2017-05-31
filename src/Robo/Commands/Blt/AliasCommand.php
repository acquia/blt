<?php

namespace Acquia\Blt\Robo\Commands\Blt;

use Acquia\Blt\Robo\BltTasks;
use Robo\Contract\VerbosityThresholdInterface;
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
      $config_file = $this->getInspector()->getCliConfigFile();
      $this->say("BLT can automatically create a Bash alias to make it easier to run BLT tasks.");
      $this->say("This alias will be created in <comment>$config_file</comment>.");
      $confirm = $this->confirm("Install alias?");
      if ($confirm) {
        $this->createNewAlias();
      }
    }
    elseif (!$this->isBltAliasUpToDate()) {
      $this->logger->warning("Your BLT alias is out of date.");
      $confirm = $this->confirm("Would you like to update it?");
      if ($confirm) {
        $this->updateAlias();
      }
    }
    else {
      $this->say("<info>The BLT alias is already installed and up to date.</info>");
    }
  }

  /**
   * Creates a new BLT alias in appropriate CLI config file.
   */
  protected function createNewAlias() {
    $this->say("Installing <comment>blt</comment> alias...");
    $config_file = $this->getInspector()->getCliConfigFile();
    if (is_null($config_file)) {
      $this->logger->error("Could not install blt alias. No profile found. Tried ~/.zshrc, ~/.bashrc, ~/.bash_profile and ~/.profile.");
    }
    else {
      $canonical_alias = file_get_contents($this->getConfigValue('blt.root') . '/scripts/blt/alias');
      $result = $this->taskWriteToFile($config_file)
        ->text($canonical_alias)
        ->append(TRUE)
        ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
        ->run();
      $this->say("<info>Added alias for blt to $config_file.</info>");
      $this->say("You may now use the <comment>blt</comment> command from anywhere within a BLT-generated repository.");
      $this->say("Restart your terminal session or run <comment>source $config_file</comment> to use the new command.");
    }
  }

  /**
   * Checks if the installed alias is up-to-date.
   *
   * @return bool
   *   TRUE if the installed alias is up to date.
   */
  protected function isBltAliasUpToDate() {
    $alias_info = $this->getAliasInfo();

    return trim($alias_info['alias']) === trim($alias_info['canonical_alias']);
  }

  /**
   * Gets information about the installed BLT alias.
   *
   * @return array
   *   An array of information about the installed BLT alias.
   */
  protected function getAliasInfo() {
    $alias_length = NULL;
    $alias = NULL;
    $config_file = $this->getInspector()->getCliConfigFile();
    $contents = file_get_contents($config_file);
    $needle = 'function blt() {';
    $begin_alias_pos = strpos($contents, $needle);
    $end_alias_pos = $this->getClosingBracketPosition($contents, $begin_alias_pos + strlen($needle));
    $canonical_alias = file_get_contents($this->getConfigValue('blt.root') . '/scripts/blt/alias');

    if (!is_null($end_alias_pos)) {
      $alias_length = $end_alias_pos - $begin_alias_pos + 1;
      $alias = substr($contents, $begin_alias_pos, $alias_length);
    }

    return [
      'config_file' => $config_file,
      'contents' => $contents,
      'start_pos' => $begin_alias_pos,
      'end_pos' => $end_alias_pos,
      'length' => $alias_length,
      'alias' => $alias,
      'canonical_alias' => $canonical_alias,
    ];
  }

  /**
   * Replaces installed alias with up-to-date alias.
   */
  protected function updateAlias() {
    $alias_info = $this->getAliasInfo();
    $new_contents = str_replace($alias_info['alias'], $alias_info['canonical_alias'], $alias_info['contents']);
    file_put_contents($alias_info['config_file'], $new_contents);
    $this->say("<info>The <comment>blt</comment> alias was updated in {$alias_info['config_file']}");
    $this->say("Execute <comment>source {$alias_info['config_file']}</comment> to update your terminal session.");
  }

  /**
   * Find the position of a closing bracket for a given stanza in a string.
   *
   * @param $contents
   *   The string containing the brackets.
   * @param int $start_pos
   *   The position of the opening bracket in the string that should be matched.
   *
   * @return int|null
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
