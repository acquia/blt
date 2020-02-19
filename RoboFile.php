<?php

use Acquia\Blt\Robo\Exceptions\BltException;
use Github\Api\Issue;
use Github\Client;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Robo\Contract\VerbosityThresholdInterface;
use Robo\Tasks;

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends Tasks implements LoggerAwareInterface {

  use LoggerAwareTrait;

  /**
   * BLT root.
   *
   * @var string
   */
  protected $bltRoot;

  /**
   * Binary.
   *
   * @var string
   */
  protected $bin;

  /**
   * Drupal PHPCS standard.
   *
   * @var string
   */
  protected $drupalPhpcsStandard;

  /**
   * PHPCS paths.
   *
   * @var string
   */
  protected $phpcsPaths;

  const BLT_DEV_BRANCH = "10.x";
  const BLT_PROJECT_DIR = "../blted8";

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
   * Executes pre-release tests against blt-project self::BLT_DEV_BRANCH.
   */
  public function releaseTest() {
    $task = $this->taskExecStack()
      ->printMetadata(TRUE)
      ->exec("{$this->bltRoot}/vendor/bin/robo sniff-code --load-from {$this->bltRoot}");

    $phpunit_group = getenv('PHPUNIT_GROUP');
    $phpunit_exclude_group = getenv('PHPUNIT_EXCLUDE_GROUP');
    $phpunit_filter = getenv('PHPUNIT_FILTER');
    $phpunit_command_string = "{$this->bltRoot}/vendor/bin/phpunit";
    if ($phpunit_group) {
      $phpunit_command_string .= " --group=" . $phpunit_group;
    }
    if ($phpunit_exclude_group) {
      $phpunit_command_string .= " --exclude-group=" . $phpunit_exclude_group;
    }
    if ($phpunit_filter) {
      $phpunit_command_string .= " --filter " . $phpunit_filter;
    }
    $task->exec($phpunit_command_string);

    $result = $task->run();

    return $result;
  }

  /**
   * Generates release notes and cuts a new tag on GitHub.
   *
   * @param string $tag
   *   The tag name. E.g, 8.6.10.
   * @param string $github_token
   *   A github access token.
   * @param array $options
   *   Options.
   *
   * @return int
   *   The CLI status code.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   *
   * @command release
   *
   * @option prev-tag The previous tag on the current branch from which to
   *   determine diff.
   */
  public function bltRelease(
    $tag,
    $github_token,
    array $options = [
      'prev-tag' => NULL,
    ]
  ) {
    $this->stopOnFail();
    $current_branch = $this->getCurrentBranch();
    $this->checkDirty();
    $this->printReleasePreamble($tag, $current_branch);
    $this->assertBranchMatchesUpstream($current_branch);
    $this->resetLocalBranch($current_branch);
    $this->updateBltVersionConstant($tag);
    $prev_tag = $this->getPrevTag($options, $current_branch);
    $release_notes = $this->generateReleaseNotes($prev_tag, $tag, $github_token);
    $this->updateChangelog($tag, $release_notes);

    // Push the change upstream.
    $this->_exec("git add CHANGELOG.md $this->bltRoot/src/Robo/Blt.php");
    $this->_exec("git commit -m 'Updating CHANGELOG.md and setting version for $tag.' -n");
    $this->_exec("git push upstream $current_branch");
    $this->createGitHubRelease($current_branch, $tag, $release_notes, $github_token);

    return 0;
  }

  /**
   * Pushes to acquia/blt-project.
   *
   * @param array $options
   *   Options.
   *
   * @command subtree:push:blt-project
   *
   * @option branch (optional) The branch to push to. Defaults to current branch.
   */
  public function subtreePushBltProject(array $options = [
    'branch' => NULL,
  ]) {
    $this->say("Pushing to acquia/blt-project");
    $prefix = "subtree-splits/blt-project";
    $url = "git@github.com:acquia/blt-project.git";
    if (!$options['branch']) {
      $options['branch'] = $this->getCurrentBranch();
    }
    $this->_exec("git subtree add --prefix $prefix $url {$options['branch']} --squash");
    $this->_exec("git subtree pull --prefix $prefix $url {$options['branch']} --squash");
    $this->_exec("git subtree push --prefix $prefix $url {$options['branch']} --squash");
  }

  /**
   * Pushes to acquia/blt-require-dev.
   *
   * @param array $options
   *   Options.
   *
   * @command subtree:push:blt-require-dev
   *
   * @option branch (optional) The branch to push to. Defaults to current branch.
   */
  public function subtreePushBltRequireDev(array $options = [
    'branch' => NULL,
  ]) {
    $this->say("Pushing to acquia/blt-require-dev");
    $prefix = "subtree-splits/blt-require-dev";
    $url = "git@github.com:acquia/blt-require-dev.git";
    if (!$options['branch']) {
      $options['branch'] = $this->getCurrentBranch();
    }
    $this->_exec("git subtree add --prefix $prefix $url {$options['branch']} --squash");
    $this->_exec("git subtree pull --prefix $prefix $url {$options['branch']} --squash");
    $this->_exec("git subtree push --prefix $prefix $url {$options['branch']} --squash");
  }

  /**
   * Generate release notes.
   *
   * @param string $prev_tag
   *   Previous tag.
   * @param string $tag
   *   Tag.
   * @param string $github_token
   *   Github token.
   *
   * @return string
   *   String.
   */
  protected function generateReleaseNotes($prev_tag, $tag, $github_token) {
    $log = $this->getChangesOnBranchSinceTag($prev_tag);
    $changes = $this->sortChanges($log, $github_token);

    $text = '';
    $text .= "[Full Changelog](https://github.com/acquia/blt/compare/$prev_tag...$tag)\n\n";
    if (!empty($changes['breaking'])) {
      $text .= "**Breaking changes**\n\n";
      $text .= $this->processReleaseNotesSection($changes['breaking']);
    }
    if (!empty($changes['enhancements'])) {
      $text .= "\n**Implemented enhancements**\n\n";
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
   * Sniffs BLT internal code via PHPCS.
   *
   * @command sniff-code
   */
  public function sniffCode() {
    $task = $this->taskExecStack()
      ->dir($this->bltRoot)
      ->exec("{$this->bin}/phpcs")
      ->exec("composer validate");
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
      // Test group:
      // @codingStandardsIgnoreStart
      // const VERSION = '9.x-dev';
      // const VERSION = '9.0.x-dev';
      // const VERSION = '9.0.0-alpha1';
      // const VERSION = '9.0.0-beta2';
      // const VERSION = '9.0.0';
      // @codingStandardsIgnoreEnd
      ->regex('/(const VERSION = \')[0-9]{1,2}\.[0-9x]{1,2}(\.[0-9x](-(alpha|beta|rc|dev)[0-9]{0,2})?|-dev?)(\';)/')
      ->to('${1}' . $tag . '${5}')
      ->run();
  }

  /**
   * Get changes.
   *
   * @param string $prev_tag
   *   Previous tag.
   *
   * @return array
   *   Array.
   */
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
      preg_match("/([a-f0-9]{40}) (.+)/", $line, $matches);
      $commit_hash = $matches[1];
      $changes[$commit_hash] = $matches[2];
    }

    return $changes;
  }

  /**
   * Get last tag.
   *
   * @param string $current_branch
   *   Current branch.
   *
   * @return mixed
   *   Mixed.
   */
  protected function getLastTagOnBranch($current_branch) {
    // List all tags, sort numerically, and filter out any that aren't numeric.
    $output = $this->taskExecStack()
      ->exec("git -c 'versionsort.suffix=-' tag --sort=-v:refname --merged $current_branch | sed '/^[[:alpha:]]/d'")
      ->interactive(FALSE)
      ->silent(TRUE)
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run()
      ->getMessage();
    $tags_on_branch = explode("\n", $output);
    $prev_tag = reset($tags_on_branch);

    return $prev_tag;
  }

  /**
   * Get current branch.
   *
   * @return string
   *   String.
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
   * Update changelog.
   *
   * @param string $tag
   *   Tag.
   * @param string $changes
   *   Changes.
   */
  protected function updateChangelog($tag, $changes) {
    $this->taskChangelog('CHANGELOG.md')
      ->setHeader("#### $tag (" . date("Y-m-d") . ")\n\n")
      ->anchor("# Change Log")
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
   * @param string $github_token
   *   Github token.
   *
   * @return array
   *   A multidimensional array grouped by the labels enhancement and bug.
   */
  protected function sortChanges(array $log_entries, $github_token) {
    $client = new Client();
    $client->authenticate($github_token, NULL, Client::AUTH_HTTP_TOKEN);
    /** @var \Github\Api\Issue $issue_api */
    $issue_api = $client->api('issue');

    $changes = [
      'breaking' => [],
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
   * @param string $log_entry
   *   Log entry.
   * @param string $issue_api
   *   Issue api.
   * @param array $changes
   *   Changes.
   *
   * @return mixed
   *   Mixed.
   */
  protected function sortLogEntry($log_entry, $issue_api, array $changes) {
    $sorted = FALSE;
    $github_issue_number = $this->parseGitHubIssueNumber($log_entry);
    if ($github_issue_number) {
      $labels = $this->getGitHubIssueLabels($issue_api, $github_issue_number);
      if ($labels) {
        foreach ($labels as $label) {
          if (strtolower($label['name']) == 'change record') {
            $changes['breaking'][] = $log_entry;
            $sorted = TRUE;
            break;
          }
          elseif (strtolower($label['name']) == 'enhancement') {
            $changes['enhancements'][] = $log_entry;
            $sorted = TRUE;
            break;
          }
          elseif (strtolower($label['name']) == 'bug') {
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
   * Parse Github issue.
   *
   * @param string $row
   *   Row.
   *
   * @return null
   *   Issue num.
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
   * Github issue labels.
   *
   * @param \Github\Api\Issue $issue_api
   *   Issue API.
   * @param string $github_issue_number
   *   Issue number.
   *
   * @return array|bool
   *   Labels.
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
   *   A string containing the formatted and imploded contents of $rows.
   */
  protected function processReleaseNotesSection(array $rows) {
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
   * Checks to see if current git branch has uncommitted changes.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
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
   * Print release preamble.
   *
   * @param string $tag
   *   Tag.
   * @param string $current_branch
   *   Current branch.
   */
  protected function printReleasePreamble($tag, $current_branch) {
    $this->logger->warning("Please run all release tests before executing this command!");
    $this->say("To run release tests, execute <comment>./vendor/bin/robo test</comment>");
    $this->output()->writeln('');
    $this->say("Continuing will do the following:");
    $this->say("- <comment>Destroy any uncommitted work on the current branch.</comment>");
    $this->say("- Hard reset to upstream/$current_branch");
    $this->say("- Update and <comment>commit</comment> CHANGELOG.md");
    $this->say("- <comment>Push</comment> $current_branch to upstream");
    $this->say("- Create a $tag release in GitHub with release notes");
  }

  /**
   * Get prev tag.
   *
   * @param array $options
   *   Options.
   * @param string $current_branch
   *   Current branch.
   *
   * @return mixed
   *   Mixed.
   */
  protected function getPrevTag(array $options, $current_branch) {
    if (!empty($options['prev-tag'])) {
      return $options['prev-tag'];
    }
    else {
      return $this->getLastTagOnBranch($current_branch);
    }
  }

  /**
   * Create github release.
   *
   * @param mixed $commitish
   *   Committish.
   * @param mixed $tag
   *   Tag.
   * @param mixed $description
   *   Description.
   * @param string $github_token
   *   Github token.
   * @param string $uri
   *   Uri.
   */
  protected function createGitHubRelease(
    $commitish,
    $tag,
    $description,
    $github_token,
    $uri = 'acquia/blt'
  ) {
    $result = $this->taskGitHubRelease($tag)
      ->uri($uri)
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
   * Current branch.
   *
   * @param string $current_branch
   *   Current branch.
   */
  protected function resetLocalBranch($current_branch) {
    // Clean up all staged and unstaged files on current branch.
    $this->taskGitStack()
      ->exec('clean -fd .')
      ->exec('remote update upstream')
      ->exec("reset --hard upstream/$current_branch")
      ->run();
  }

  /**
   * Assert branch matches upstream.
   *
   * @param string $current_branch
   *   Current branch.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  protected function assertBranchMatchesUpstream($current_branch) {
    $branch_matches_upstream = $this->_exec("git diff $current_branch upstream/$current_branch --quiet")->wasSuccessful();
    if (!$branch_matches_upstream) {
      $this->logger->warning("$current_branch does not match upstream/$current_branch.");
      $this->logger->warning("Continuing will cause you to lose all local changes!");
      $continue = $this->confirm("Continue?");
      if (!$continue) {
        throw new BltException("Release terminated by user.");
      }
    }
  }

}
