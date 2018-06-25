<?php

namespace Acquia\Blt\Robo\Commands\Blt;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;
use Robo\Contract\VerbosityThresholdInterface;

/**
 * Defines commands for installing and updating the BLT alias.
 */
class AliasCommand extends BltTasks {

  /**
   * Installs the BLT alias for command line usage.
   *
   * @command blt:init:shell-alias
   *
   * @aliases alias install-alias
   */
  public function installBltAlias() {
    if (!$this->getInspector()->isBltAliasInstalled()) {
      $config_file = $this->getInspector()->getCliConfigFile();
      if (is_null($config_file)) {
        $this->logger->warning("Could not find your CLI configuration file.");
        $this->logger->warning("Looked in ~/.zsh, ~/.bash_profile, ~/.bashrc, ~/.profile, and ~/.functions.");
        $created = $this->createBashProfile();
        if (!$created) {
          $this->logger->warning("Please create one of the aforementioned files, or create the BLT alias manually.");
        }
      }
      else {
        $this->say("BLT can automatically create a Bash alias to make it easier to run BLT tasks.");
        $this->say("This alias will be created in <comment>$config_file</comment>.");
        $confirm = $this->confirm("Install alias?");
        if ($confirm) {
          $this->createNewAlias();
        }
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
      $this->logger->error("Could not install blt alias. No profile found. Tried ~/.zshrc, ~/.bashrc, ~/.bash_profile, ~/.profile, and ~/.functions.");
    }
    else {
      $canonical_alias = file_get_contents($this->getConfigValue('blt.root') . '/scripts/blt/alias');
      $result = $this->taskWriteToFile($config_file)
        ->text($canonical_alias)
        ->append(TRUE)
        ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
        ->run();

      if (!$result->wasSuccessful()) {
        throw new BltException("Unable to install BLT alias.");
      }

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
    $bytes = file_put_contents($alias_info['config_file'], $new_contents);
    if (!$bytes) {
      throw new BltException("Could not update BLT alias in {$alias_info['config_file']}.");
    }

    $this->say("<info>The <comment>blt</comment> alias was updated in {$alias_info['config_file']}");
    $this->say("Execute <comment>source {$alias_info['config_file']}</comment> to update your terminal session.");
    $this->say("You may then execute <comment>blt</comment> commands.");
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
   * Creates a ~/.bash_profile on supporting systems if one does not exist.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  protected function createBashProfile() {
    $inspector = $this->getInspector();
    if ($inspector->isOsx() || $inspector->isAhEnv()) {
      $continue = $this->confirm("Would you like to create ~/.bash_profile?");
      if ($continue) {
        $user = posix_getpwuid(posix_getuid());
        $home_dir = $user['dir'];
        $bash_profile = $home_dir . '/.bash_profile';
        if (!file_exists($bash_profile)) {
          $result = $this->taskFilesystemStack()
            ->touch($bash_profile)
            ->run();

          if (!$result->wasSuccessful()) {
            throw new BltException("Could not create $bash_profile.");
          }

          return TRUE;
        }
      }
    }

    return FALSE;
  }

}
