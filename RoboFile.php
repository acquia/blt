<?php

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
  protected $currentBranch;
  protected $tag;
  protected $prevTag;
  protected $date;
  protected $gitHubToken;

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
   * Generates release notes and cuts a new tag on GitHub.
   *
   * @command release
   *
   * @param string $tag
   *   The tag name. E.g, 8.6.10.
   * @param string $github_token
   *   A github access token.
   *
   * @option $update-changelog Update CHANGELOG.md. Defaults to true.
   *
   * @hidden
   *
   * @return int
   *   The CLI status code.
   */
  public function bltRelease(
    $tag,
    $github_token
  ) {
    $this->stopOnFail();

    $this->currentBranch = $this->getCurrentBranch();

    // @todo Check to see if git branch is dirty.
    $this->logger->warning("Please run all release tests before executing this command!");
    $this->say("To run release tests, execute ./scripts/blt/pre-release-tests.sh");
    $this->output()->writeln('');
    $this->say("Continuing will do the following:");
    $this->say("- <comment>Destroy any uncommitted work on the current branch.</comment>");
    $this->say("- Hard reset to origin/{$this->currentBranch}");
    $this->say("- Update and <comment>commit</comment> CHANGELOG.md");
    $this->say("- <comment>Push</comment> {$this->currentBranch} to origin");
    $this->say("- Create a $tag release in GitHub with release notes");
    $continue = $this->confirm("Continue?");

    if (!$continue) {
      return 0;
    }

    $this->gitHubToken = $github_token;
    $this->tag = $tag;
    $this->prevTag = $this->getLastTagOnBranch($this->currentBranch);
    $this->date = date("Y-m-d");

    $branch_exists_upstream = $this->taskExecStack()
      ->exec("git ls-remote --exit-code . origin/{$this->currentBranch} &> /dev/null")
      ->silent(TRUE)
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run()
      ->wasSuccessful();
    if (!$branch_exists_upstream) {
      $this->logger->error("{$this->currentBranch} does not exist on the origin remote!");
      $this->say("Please run <comment>git push origin {$this->currentBranch}</comment>");
      return 1;
    }

    // Clean up all staged and unstaged files on current branch.
    $this->taskGitStack()
      ->exec('clean -fd .')
      ->exec('remote update')
      // @todo Check to see if branch doesn't match, confirm with dialog.
      ->exec("reset --hard origin/{$this->currentBranch}");
    // ->run();
    $this->changeVersionConstant($tag);
    $changes = $this->generateReleaseNotes($this->currentBranch, $tag);
    $this->updateChangelog($tag, $changes);

    // Push the change upstream.
    $this->_exec("git add CHANGELOG.md $this->bltRoot/src/Robo/Blt.php");
    $this->_exec("git commit -m 'Updating CHANGELOG.md for {$tag}.' -n");
    $this->_exec("git push origin {$this->currentBranch}");

    $result = $this->taskGitHubRelease($tag)
      ->uri('acquia/blt')
      ->comittish($this->currentBranch)
      ->name($tag)
      ->description($changes)
      ->draft(TRUE)
      ->accessToken($github_token)
      ->openBrowser(TRUE)
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    return 0;
  }

  /**
   * Update CHANGELOG.md with notes for new release.
   *
   * @return int
   *   The CLI status code.
   */
  public function releaseNotes($tag) {
    // @todo Check git version.
    $changes = $this->generateReleaseNotes($this->currentBranch, $tag);
    $this->updateChangelog($tag, $changes);
  }

  /**
   * @param $current_branch
   *
   * @return string
   */
  protected function generateReleaseNotes($current_branch) {
    $prev_tag = $this->getLastTagOnBranch($current_branch);
    $log = $this->getChangesOnBranchSinceTag($prev_tag);
    $changes = $this->sortChanges($log);

    $text = '';
    $text .= "[Full Changelog](https://github.com/acquia/blt/compare/{$this->prevTag}...{$this->tag})\n\n";
    if (!empty($changes['enchancements'])) {
      $text .= "**Implemented enchancements**\n\n";
      $text .= $this->processReleaseNotesSection($changes['enchancements']);
    }
    if (!empty($changes['bugs'])) {
      $text .= "\n\n**Fixed bugs**\n\n";
      $text .= $this->processReleaseNotesSection($changes['bugs']);
    }
    if (!empty($changes['misc'])) {
      $text .= "\n\n**Miscellaneous**\n\n";
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
   * @param $tag
   */
  protected function changeVersionConstant($tag) {
    // Change version constant in Blt.php.
    $this->taskReplaceInFile($this->bltRoot . '/src/Robo/Blt.php')
      ->regex('/(const VERSION = \')([0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,2})(\';)/')
      ->to('${1}' . $tag . '${3}')
      ->run();
  }

  /**
   * @param $prev_tag
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
      ->setHeader("#### {$this->tag} ({$this->date})\n\n")
      ->setBody($changes)
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();
  }

  /**
   * @param $log
   *
   * @return array
   */
  protected function sortChanges($log) {
    $client = new Client();
    $client->authenticate($this->gitHubToken, NULL, Client::AUTH_URL_TOKEN);
    /** @var \Github\Api\Issue $issue_api */
    $issue_api = $client->api('issue');

    $changes = [
      'enchancements' => [],
      'bugs' => [],
      'misc' => [],
    ];
    foreach ($log as $row) {
      $sorted = FALSE;
      $found_match = preg_match("/(((fix(es|ed)?)|(close(s|d)?)|(resolve(s|d)?)) )?#([[:digit:]]+)|#[[:digit:]]+/",
        $row, $matches);
      if ($found_match) {
        $issue_num = $matches[9];
        $issue = $issue_api->show('acquia', 'blt', $issue_num);
        if (isset($issue['labels'])) {
          foreach ($issue['labels'] as $label) {
            if ($label['name'] == 'enhancement') {
              $changes['enchancements'][] = $row;
              $sorted = TRUE;
              break;
            }
            elseif ($label['name'] == 'bug') {
              $changes['bugs'][] = $row;
              $sorted = TRUE;
              break;
            }
          }
        }
      }
      if (!$sorted) {
        $changes['misc'][] = $row;
      }
    }
    return $changes;
  }

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

}
