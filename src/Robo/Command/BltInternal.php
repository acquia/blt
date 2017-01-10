<?php

namespace Acquia\Blt\Robo\Command;

use Robo\Tasks;

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class BltInternal extends Tasks
{

  /**
   * Generates release notes and cuts a new tag on GitHub.
   *
   * @command blt:release
   *
   * @param string $tag The tag name. E.g, 8.6.10
   * @param string $github_token A github access token
   *
   * @return int
   *   The status code.
   */
  public function bltRelease($tag, $github_token)
  {

    $requirements_met = $this->checkCommandsExist([
      'git',
      'github_changelog_generator',
    ]);
    if (!$requirements_met) {
      return 1;
    }

    $this->yell("This will destroy any uncommitted work on the current branch. It will also hard reset 8.x and 8.x-release to match the upstream history.");
    $continue = $this->confirm("Continue?");

    if ($continue) {
      // Clean up all staged and unstaged files on current branch.
      $this->_exec('git clean -fd .');
      $this->_exec('git remote update');
      $this->_exec('git reset --hard');

      // Reset to match upstream history of 8.x.
      $this->_exec('git checkout 8.x');
      $this->_exec('git reset --hard origin/8.x');

      // Generate release notes.
      $partial_changelog_filename = 'CHANGELOG.partial';
      $this->_exec("github_changelog_generator --token=$github_token --future-release=$tag --output=$partial_changelog_filename");
      $partial_changelog_contents = file_get_contents($partial_changelog_filename);
      // Remove last 3 lines.
      $trimmed_partial_changelog = implode("\n", array_slice(explode("\n", $partial_changelog_contents), -3));
      print $trimmed_partial_changelog;
    }
  }

    /**
     * Check if an array of commands exists on the system.
     *
     * @param $commands array An array of command binaries.
     *
     * @return bool
     *   TRUE if all commands exist, otherwise FALSE.
     */
    protected function checkCommandsExist($commands) {
      foreach ($commands as $command) {
        if (!$this->commandExists($command)) {
          $this->yell("Unable to find '$command' command!");
          return FALSE;
        }
      }

      return TRUE;
    }

    /**
     * Checks if a given command exists on the system.
     *
     * @param $command string the command binary only. E.g., "drush" or "php".
     *
     * @return bool
     *   TRUE if the command exists, otherwise FALSE.
     */
    protected function commandExists($command) {
      return $this->taskExec("command -v $command >/dev/null 2>&1")->run()->wasSuccessful();
    }
}
