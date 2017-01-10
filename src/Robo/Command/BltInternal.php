<?php

namespace Acquia\Blt\Robo\Command;

use Robo\Tasks;
use GuzzleHttp\Client;

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
   *   The CLI status code.
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

    // @todo Check to see if git branch is dirty.
    $this->say("This will do the following:");
    $this->say("- <error>Destroy any uncommitted work on the current branch.</error>");
    $this->say("- <error>Hard reset 8.x and 8.x-release to match the upstream history.</error>");
    $this->say("- Merge 8.x into 8.x-release");
    $this->say("- Push 8.x-release to origin");
    $this->say("- Create a $tag release in GitHub with release notes");
    $continue = $this->confirm("Continue?");

    if (!$continue) {
      return 0;
    }
      // Clean up all staged and unstaged files on current branch.
      $this->_exec('git clean -fd .');
      $this->_exec('git remote update');
      // @todo Check to see if branch doesn't match, confirm with dialog.
      $this->_exec('git reset --hard');

      // Reset local 8.x to match upstream history of 8.x.
      $this->_exec('git checkout 8.x');
      // @todo Check to see if branch doesn't match, confirm with dialog.
      $this->_exec('git reset --hard origin/8.x');

      // Reset local 8.x-release to match upstream history of 8.x-release.
      $this->_exec('git checkout 8.x-release');
      // @todo Check to see if branch doesn't match, confirm with dialog.
      $this->_exec('git reset --hard origin/8.x-release');

      // Merge 8.x into 8.x-release and push.
      $this->_exec('git merge 8.x');
      $this->_exec('git push origin 8.x-release');

      $partial_release_notes = $this->generateReleaseNotes($tag, $github_token);
      $trimmed_release_notes = $this->trimStartingLines($partial_release_notes, 3);

      $request_payload = [
        'tag_name' => $tag,
        'name' => $tag,
        'target_commitish' => '8.x-release',
        'body' => $trimmed_release_notes,
        'draft' => true,
        'prerelease' => true,
      ];

    $client = new Client([
      // Base URI is used with relative requests
      'base_uri' => 'https://api.github.com/repos/acquia/blt/',
      'query' => [
        'access_token' => $github_token,
      ],
    ]);
    // @todo Check to see if release already exists, update if it does.
    $response = $client->request('POST', 'releases', [
      'json' => $request_payload,
    ]);
    if ($response->getStatusCode() != 201) {
      $this->yell("Something went wrong when attempting to create release $tag.");
      $this->say($response->getBody());
    }

    $response_body = json_decode($response->getBody(), TRUE);
    $this->say("Release $tag has been created on GitHub: \n");
    $this->_exec("open {$response_body['html_url']}");
  }

  /**
   * Update CHANGELOG.md with notes for new release.
   *
   * @param string $tag The tag name. E.g, 8.6.10
   * @param string $github_token A github access token
   *
   * @return int
   *   The CLI status code.
   */
  public function bltReleaseNotes($tag, $github_token) {

    $requirements_met = $this->checkCommandsExist([
      'github_changelog_generator',
    ]);
    if (!$requirements_met) {
      return 1;
    }

    // @todo Check to see if git branch is dirty.
    $this->yell("You should execute this command on a clean, updated checkout of 8.x.");
    $continue = $this->confirm("Continue?");

    if (!$continue) {
      return 0;
    }

    if (!$trimmed_partial_changelog = $this->generateReleaseNotes($tag, $github_token)) {
      $this->yell("Failed to generate release notes");
      return 1;
    }

    // Remove first 4 lines from full changelog.
    $full_changelog_filename = 'CHANGELOG.md';
    $full_changelog = file_get_contents($full_changelog_filename);
    $trimmed_full_changelog = $this->trimStartingLines($full_changelog, 1);

    $new_full_changelog = $trimmed_partial_changelog . $trimmed_full_changelog;
    file_put_contents($full_changelog_filename, $new_full_changelog);

    $this->say("$full_changelog_filename has been updated and committed. Please push to origin.");
    $this->_exec("git add $full_changelog_filename");
    $this->_exec("git commit -m 'Updating $full_changelog_filename with $tag release notes.'");

    return 0;
  }

  /**
   * Generate notes for new release.
   *
   * @param $tag
   * @param $github_token
   *
   * @return int|string
   *   FALSE on failure, otherwise the release notes.
   */
  protected function generateReleaseNotes($tag, $github_token) {
    // Generate release notes.
    $partial_changelog_filename = 'CHANGELOG.partial';
    if (!$this->taskExec("github_changelog_generator --token=$github_token --future-release=$tag --output=$partial_changelog_filename")->run()->wasSuccessful()) {
      $this->yell("Unable to generate CHANGELOG using github_changelog_generator.");
      return 1;
    }

    // Remove last 3 lines from new, partial changelog.
    $partial_changelog_contents = file_get_contents($partial_changelog_filename);
    $trimmed_partial_changelog = $this->trimEndingLines($partial_changelog_contents, 3);
    unlink($partial_changelog_filename);

    return $trimmed_partial_changelog;
  }

  /**
   * Trims the last $num_lines lines from end of a text string.
   *
   * @param string $text A string of text.
   * @param int $num_lines The number of lines to trim from the end of the text.
   *
   * @return string
   *   The trimmed text.
   */
  protected function trimEndingLines($text, $num_lines) {
    return implode("\n", array_slice(explode("\n", $text), 0, sizeof($text) - $num_lines));
  }

  /**
   * Trims the last $num_lines lines from beginning of a text string.
   *
   * @param string $text A string of text.
   * @param int $num_lines The number of lines to trim from beginning of text.
   *
   * @return string
   *   The trimmed text.
   */
  protected function trimStartingLines($text, $num_lines) {
    return implode("\n", array_slice(explode("\n", $text), $num_lines));
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
