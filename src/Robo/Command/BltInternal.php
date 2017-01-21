<?php

namespace Acquia\Blt\Robo\Command;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Common\BltIO;
use Acquia\Blt\Robo\Common\LocalEnvironmentValidator;
use GuzzleHttp\Client;

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class BltInternal extends BltTasks {

  use LocalEnvironmentValidator;

  /**
   * Generates release notes and cuts a new tag on GitHub.
   *
   * @command blt:release
   *
   * @param string $tag
   *   The tag name. E.g, 8.6.10.
   * @param string $github_token
   *   A github access token.
   *
   * @option $update-changelog Update CHANGELOG.md. Defaults to true.
   *
   * @return int
   *   The CLI status code.
   */
  public function bltRelease($tag, $github_token, $opts = ['update-changelog' => TRUE]) {
    $requirements_met = $this->checkCommandsExist([
      'git',
      'github_changelog_generator',
    ]);
    if (!$requirements_met) {
      return 1;
    }

    // @todo Check to see if git branch is dirty.
    $this->warn("Please run all release tests before executing this command!");
    $this->say("To run release tests, execute ./scripts/blt/pre-release-tests.sh");
    $this->output()->writeln('');
    $this->say("Continuing will do the following:");
    $this->say("- <comment>Destroy any uncommitted work on the current branch.</comment>");
    $this->say("- <comment>Hard reset 8.x and 8.x-release to match the upstream history.</comment>");
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

    if (!$tag_release_notes = $this->generateReleaseNotes($tag, $github_token)) {
      $this->yell("Failed to generate release notes.");
      return 1;
    }

    if ($opts['update-changelog']) {
      $this->updateChangelog($tag, $tag_release_notes);
      $this->say("<comment>If you continue, this commit will be pushed upstream and a release will be created.</comment>");
      $continue = $this->confirm("Continue?");
      if (!$continue) {
        $this->_exec("git reset --hard HEAD~1");
        return 0;
      }
    }

    // Push the change upstream.
    $this->_exec("git push origin 8.x");

    // Reset local 8.x-release to match upstream history of 8.x-release.
    $this->_exec('git checkout 8.x-release');
    // @todo Check to see if branch doesn't match, confirm with dialog.
    $this->_exec('git reset --hard origin/8.x-release');

    // Merge 8.x into 8.x-release and push.
    $this->_exec('git merge 8.x');
    $this->_exec('git push origin 8.x-release');

    $this->createGitHubRelease($tag, $github_token, $tag_release_notes);

    return 0;
  }

  /**
   * Create a new release on GitHub.
   *
   * @param string $tag
   *   The tag name. E.g, 8.6.10.
   * @param string $github_token
   *   A github access token.
   * @param string $tag_release_notes
   *
   *   The release notes for this specific tag.
   */
  protected function createGitHubRelease($tag, $github_token, $tag_release_notes) {
    $request_payload = [
      'tag_name' => $tag,
      'name' => $tag,
      'target_commitish' => '8.x-release',
      'body' => $this->trimStartingLines($tag_release_notes, 3),
      'draft' => TRUE,
      'prerelease' => TRUE,
    ];

    $client = new Client([
      // Base URI is used with relative requests.
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
   * @param string $tag
   *   The tag name. E.g, 8.6.10.
   * @param string $github_token
   *   A github access token.
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

    if (!$tag_release_notes = $this->generateReleaseNotes($tag, $github_token)) {
      $this->yell("Failed to generate release notes");
      return 1;
    }

    $this->updateChangelog($tag, $tag_release_notes);

    return 0;
  }

  /**
   * @param $tag_release_notes
   */
  protected function updateChangelog($tag, $tag_release_notes) {
    // Remove first 4 lines from full changelog.
    $full_changelog_filename = 'CHANGELOG.md';
    $full_changelog = file_get_contents($full_changelog_filename);
    $trimmed_full_changelog = $this->trimStartingLines($full_changelog, 1);

    $new_full_changelog = $tag_release_notes . $trimmed_full_changelog;
    file_put_contents($full_changelog_filename, $new_full_changelog);

    $this->say("$full_changelog_filename has been updated and committed. Please push to origin.");
    $this->_exec("git add $full_changelog_filename");
    $this->_exec("git commit -m 'Updating $full_changelog_filename with $tag release notes.'");
    $this->yell("Release notes for $tag were added and committed to CHANGELOG.md. Please review the commit:");
    $this->_exec("git show");
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

}
