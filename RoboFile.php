<?php

use Acquia\Blt\Robo\Exceptions\BltException;
use Github\Api\Issue;
use Robo\Contract\VerbosityThresholdInterface;
use Github\Client;
use Robo\Tasks;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends Tasks implements LoggerAwareInterface {

  use LoggerAwareTrait;

  protected $bltRoot;
  protected $bin;
  protected $drupalPhpcsStandard;
  protected $phpcsPaths;

  /**
   * This hook will fire for all commands in this command file.
   *
   * @hook init
   */
  public function initialize() {
    $this->bltRoot = __DIR__;
    $this->bin = $this->bltRoot . '/vendor/bin';
  }

  /**
   * Executes pre-release tests against blt-project 9.x-dev.
   *
   * @option base-branch The blt-project (NOT blt) branch to test.
   */
  public function test($options = [
    'base-branch' => '9.x',
  ]) {
    $this->stopOnFail();
    $test_project_dir = $this->bltRoot . "/../blted8";
    if (file_exists($test_project_dir . "/.vagrant")) {
      $this->taskExecStack()
        ->exec("vagrant destroy")
        ->dir($test_project_dir)
        ->run();
    }
    if (file_exists($test_project_dir)) {
      $this->logger->warning("This will destroy the $test_project_dir directory!");
      $this->say("If you did not execute tests using `sudo`, this may fail.");
      $continue = $this->confirm("Continue?");
      if (!$continue) {
        return 1;
      }
    }

    $this->taskDeleteDir($test_project_dir)->run();
    $this->taskExecStack()
      ->dir($this->bltRoot . "/..")
      ->exec("COMPOSER_PROCESS_TIMEOUT=2000 composer create-project acquia/blt-project:{$options['base-branch']}-dev blted8 --no-interaction")
      ->run();

    $bin = $test_project_dir . "/vendor/bin";
    $continue = $this->confirm("Ready to boot VM?");
    if (!$continue) {
      return 1;
    }
    $this->taskExecStack()
      ->dir($test_project_dir)
      ->exec("$bin/blt vm --yes")
      ->exec("$bin/blt validate")
      ->exec("$bin/blt setup")
      ->exec("$bin/blt tests")
      ->run();

    $this->say("<info>Completed testing on VM.</info>");
    $continue = $this->confirm("Destroy VM and continue?");
    if (!$continue) {
      // Not really a failure.
      return 0;
    }
    $this->taskExecStack()
      ->dir($test_project_dir)
      ->exec("$bin/blt vm:nuke")
      ->run();
  }

  /**
   * Generates release notes and cuts a new tag on GitHub.
   *
   * @command release
   *
   * @param string $tag
   *   The tag name. E.g, 8.6.10.
   * @param string $github_token
   *   A github access token.
   * @option prev-tag The previous tag on the current branch from which to
   *   determine diff.
   *
   * @return int
   *   The CLI status code.
   */
  public function bltRelease(
    $tag,
    $github_token,
    $options = [
      'prev-tag' => NULL,
    ]
  ) {
    $this->stopOnFail();
    $current_branch = $this->getCurrentBranch();
    $this->branchExistsUpstream($current_branch);
    $this->checkDirty();
    $this->printReleasePreamble($tag, $current_branch);

    $continue = $this->confirm("Continue?");
    if (!$continue) {
      return 1;
    }

    $prev_tag = $this->getPrevTag($options, $current_branch);

    $branch_matches_upstream = $this->_exec("git diff $current_branch origin/$current_branch --quiet")->wasSuccessful();
    if (!$branch_matches_upstream) {
      $this->logger->warning("$current_branch does not match origin/$current_branch.");
      $this->logger->warning("Continuing will cause you to lose all local changes!");
      $continue = $this->confirm("Continue?");
      if (!$continue) {
        return 1;
      }
    }
    $this->resetLocalBranch($current_branch);
    $this->updateBltVersionConstant($tag);
    $release_notes = $this->generateReleaseNotes($prev_tag, $tag, $github_token);
    $this->updateChangelog($tag, $release_notes);

    // Push the change upstream.
    $this->_exec("git add CHANGELOG.md $this->bltRoot/src/Robo/Blt.php");
    $this->_exec("git commit -m 'Updating CHANGELOG.md for $tag.' -n");
    $this->_exec("git push origin $current_branch");
    $this->createGitHubRelease($current_branch, $tag, $release_notes, $github_token);

    return 0;
  }

  /**
   * Update CHANGELOG.md with notes for new release.
   *
   * @param string $tag
   *   The tag name. E.g, 8.6.10.
   * @param string $github_token
   *   A github access token.
   * @option prev-tag The previous tag on the current branch from which to
   *   determine diff.
   *
   * @return int
   *   The CLI status code.
   */
  public function releaseNotes(
    $tag,
    $github_token,
    $options = [
      'prev-tag' => NULL,
    ]
  ) {
    $current_branch = $this->getCurrentBranch();
    $prev_tag = $this->getPrevTag($options, $current_branch);

    // @todo Check git version.
    $changes = $this->generateReleaseNotes($tag, $prev_tag, $github_token);
    $this->updateChangelog($tag, $changes);
  }

  /**
   * @param $prev_tag
   * @param $tag
   * @param $github_token
   *
   * @return string
   */
  protected function generateReleaseNotes($prev_tag, $tag, $github_token) {
    $log = $this->getChangesOnBranchSinceTag($prev_tag);
    $changes = $this->sortChanges($log, $github_token);

    $text = '';
    $text .= "[Full Changelog](https://github.com/acquia/blt/compare/$prev_tag...$tag)\n\n";
    if (!empty($changes['enhancements'])) {
      $text .= "**Implemented enhancements**\n\n";
      $text .= $this->processReleaseNotesSection($changes['enhancements']);
    }
    if (!empty($changes['bugs'])) {
      $text .= "\n**Fixed bugs**\n\n";
      $text .= $this->processReleaseNotesSection($changes['bugs']);
    }
    if (!empty($changes['misc'])) {
      $text .= "\n**Miscellaneous**\n\n";
      $text .= $this->processReleaseNotesSection($changes['misc']);
    }

    return $text;
  }

  /**
   * Fixes BLT internal code via PHPCBF.
   *
   * @command fix-code
   */
  public function fixCode() {
    $command = "'{$this->bin}/phpcbf'";
    $task = $this->taskExecStack()
      ->dir($this->bltRoot)
      ->exec($command);
    $result = $task->run();

    return $result->getExitCode();
  }

  /**
   * Sniffs BLT internal code via PHPCS.
   *
   * @command sniff-code
   */
  public function sniffCode() {
    $command = "'{$this->bin}/phpcs'";
    $task = $this->taskExecStack()
      ->dir($this->bltRoot)
      ->exec($command);
    $result = $task->run();

    return $result->getExitCode();
  }

  /**
   * Updates the version constant in Blt.php.
   *
   * @param string $tag
   *   The new version.
   */
  protected function updateBltVersionConstant($tag) {
    // Change version constant in Blt.php.
    $this->taskReplaceInFile($this->bltRoot . '/src/Robo/Blt.php')
      ->regex('/(const VERSION = \')([0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,2})(\';)/')
      ->to('${1}' . $tag . '${3}')
      ->run();
  }

  protected function getChangesOnBranchSinceTag($prev_tag) {
    $output = $this->taskExecStack()
      ->exec("git rev-list $prev_tag..HEAD --pretty=oneline")
      ->interactive(FALSE)
      ->silent(TRUE)
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run()
      ->getMessage();
    $lines = array_filter(explode("\n", $output));
    $changes = [];
    foreach ($lines as $line) {
      $num_matches = preg_match("/([a-f0-9]{40}) (.+)/", $line, $matches);
      $commit_hash = $matches[1];
      $changes[$commit_hash] = $matches[2];
    }

    return $changes;
  }

  /**
   * @param $current_branch
   *
   * @return mixed
   */
  protected function getLastTagOnBranch($current_branch) {
    $output = $this->taskExecStack()
      ->exec("git tag --merged $current_branch")
      ->interactive(FALSE)
      ->silent(TRUE)
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run()
      ->getMessage();
    $lines = explode("\n", $output);
    $tags_on_branch = array_reverse($lines);
    $prev_tag = reset($tags_on_branch);

    return $prev_tag;
  }

  /**
   * @return string
   */
  protected function getCurrentBranch() {
    $current_branch = $this->taskExecStack()
      ->exec('git rev-parse --abbrev-ref HEAD')
      ->interactive(FALSE)
      ->silent(TRUE)
      ->run()
      ->getMessage();
    return $current_branch;
  }

  /**
   * @param $tag
   * @param $changes
   */
  protected function updateChangelog($tag, $changes) {
    $this->taskChangelog('CHANGELOG.md')
      ->setHeader("#### $tag (" . date("Y-m-d") . ")\n\n")
      ->setBody($changes)
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();
  }

  /**
   * Sorts an array of log changes based on GitHub issue labels.
   *
   * This method will iterate over an array of log changes, use a regular
   * expression to identify GitHub issue numbers, and use the GitHub API to
   * fetch the labels for those issues.
   *
   * @param array $log_entries
   *   An array of log changes. Typically each row would be a commit message.
   *
   * @return array
   *   A multidimensional array grouped by the labels enchancement and bug.
   */
  protected function sortChanges($log_entries, $github_token) {
    $client = new Client();
    $client->authenticate($github_token, NULL, Client::AUTH_URL_TOKEN);
    /** @var \Github\Api\Issue $issue_api */
    $issue_api = $client->api('issue');

    $changes = [
      'enhancements' => [],
      'bugs' => [],
      'misc' => [],
    ];
    foreach ($log_entries as $log_entry) {
      $changes = $this->sortLogEntry($log_entry, $issue_api, $changes);
    }
    return $changes;
  }

  /**
   * Sorts log entry according to GitHub label.
   *
   * @param $log_entry
   * @param $issue_api
   * @param $changes
   *
   * @return mixed
   */
  protected function sortLogEntry($log_entry, $issue_api, $changes) {
    $sorted = FALSE;
    $github_issue_number = $this->parseGitHubIssueNumber($log_entry);
    if ($github_issue_number) {
      $labels = $this->getGitHubIssueLabels($issue_api, $github_issue_number);
      if ($labels) {
        foreach ($labels as $label) {
          if ($label['name'] == 'enhancement') {
            $changes['enhancements'][] = $log_entry;
            $sorted = TRUE;
            break;
          }
          elseif ($label['name'] == 'bug') {
            $changes['bugs'][] = $log_entry;
            $sorted = TRUE;
            break;
          }
        }
      }
    }
    if (!$sorted) {
      $changes['misc'][] = $log_entry;
    }
    return $changes;
  }

  /**
   * @param $row
   *
   * @return null
   */
  protected function parseGitHubIssueNumber($row) {
    $found_match = preg_match("/(((fix(es|ed)?)|(close(s|d)?)|(resolve(s|d)?)) )?#([[:digit:]]+)|#[[:digit:]]+/",
      $row, $matches);
    if ($found_match) {
      $issue_num = $matches[9];

      return $issue_num;
    }

    return NULL;
  }

  /**
   * @param \Github\Api\Issue $issue_api
   * @param $github_issue_number
   *
   * @return array|bool
   */
  protected function getGitHubIssueLabels(Issue $issue_api, $github_issue_number) {
    $issue = $issue_api->show('acquia', 'blt', $github_issue_number);
    $labels = isset($issue['labels']) ? $issue['labels'] : [];

    return $labels;
  }

  /**
   * Processes an array of change log changes.
   *
   * Walks the array and appends prefix and suffix for markdown formatting.
   *
   * @param string[] $rows
   *   An array containing a list of changes.
   *
   * @return string
   *  A string containing the formatted and imploded contents of $rows.
   *
   */
  protected function processReleaseNotesSection($rows) {
    $text = implode(
        "\n",
        array_map(
          function ($i) {
            return "- $i";
          },
          $rows
        )
      ) . "\n";
    return $text;
  }

  /**
   * @param $current_branch
   * @param string $remote
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  protected function branchExistsUpstream($current_branch, $remote = 'origin') {
    $branch_exists_upstream = $this->taskExecStack()
      ->exec("git ls-remote --exit-code . $remote/$current_branch &> /dev/null")
      ->silent(TRUE)
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run()
      ->wasSuccessful();
    if (!$branch_exists_upstream) {
      $this->say("Please run <comment>git push $remote $current_branch</comment>");
      throw new BltException("$current_branch does not exist on the $remote remote!");
    }
  }

  /**
   * Checks to see if current git branch has uncommitted changes.
   *
   * @throws \Exception
   *   Thrown if deploy.git.failOnDirty is TRUE and there are uncommitted
   *   changes.
   */
  protected function checkDirty() {
    $result = $this->taskExec('git status --porcelain')
      ->printMetadata(FALSE)
      ->printOutput(FALSE)
      ->interactive(FALSE)
      ->run();
    if (!$result->wasSuccessful()) {
      throw new BltException("Unable to determine if local git repository is dirty.");
    }

    $dirty = (bool) $result->getMessage();
    if ($dirty) {
      throw new BltException("There are uncommitted changes, commit or stash these changes before deploying.");
    }
  }

  /**
   * @param $tag
   * @param $current_branch
   */
  protected function printReleasePreamble($tag, $current_branch) {
    $this->logger->warning("Please run all release tests before executing this command!");
    $this->say("To run release tests, execute <comment>./vendor/bin/robo test</comment>");
    $this->output()->writeln('');
    $this->say("Continuing will do the following:");
    $this->say("- <comment>Destroy any uncommitted work on the current branch.</comment>");
    $this->say("- Hard reset to origin/$current_branch");
    $this->say("- Update and <comment>commit</comment> CHANGELOG.md");
    $this->say("- <comment>Push</comment> $current_branch to origin");
    $this->say("- Create a $tag release in GitHub with release notes");
  }

  protected function getPrevTag($options, $current_branch) {
    if (!empty($options['prev-tag'])) {
      return $options['prev-tag'];
    }
    else {
      return $this->getLastTagOnBranch($current_branch);
    }
  }

  /**
   * @param $commitish
   * @param $tag
   * @param $description
   * @param $github_token
   */
  protected function createGitHubRelease(
    $commitish,
    $tag,
    $description,
    $github_token
  ) {
    $result = $this->taskGitHubRelease($tag)
      ->uri('acquia/blt')
      ->comittish($commitish)
      ->name($tag)
      ->description($description)
      ->draft(TRUE)
      ->accessToken($github_token)
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    $data = $result->getData();
    $response = $data['response'];
    $this->taskOpenBrowser($response->html_url)->run();
  }

  /**
   * @param $current_branch
   */
  protected function resetLocalBranch($current_branch) {
    // Clean up all staged and unstaged files on current branch.
    $this->taskGitStack()
      ->exec('clean -fd .')
      ->exec('remote update')
      ->exec("reset --hard origin/$current_branch")
      ->run();
  }

}
