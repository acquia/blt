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
   * @param array $options
   */
  protected function createTestApp($options = [
    'project-type' => 'standalone',
    'project-dir' => self::BLT_PROJECT_DIR,
    'vm' => TRUE,
  ]) {
    switch ($options['project-type']) {
      case 'standalone':
        $this->createFromBltProject($options);
        break;

      case 'symlink':
        $this->createFromSymlink($options);
        break;
    }
  }

  /**
   * Create a new project via symlink from current checkout of BLT.
   *
   * Local BLT will be symlinked to blted8/vendor/acquia/blt.
   *
   * @option project-dir The directory in which the test project will be
   *   created.
   * @option vm Whether a VM will be booted.
   */
  public function createFromSymlink($options = [
    'project-dir' => self::BLT_PROJECT_DIR,
    'vm' => TRUE,
  ]) {
    $test_project_dir = $this->bltRoot . "/" . $options['project-dir'];
    $bin = $test_project_dir . "/vendor/bin";
    $this->prepareTestProjectDir($test_project_dir);
    $this->taskFilesystemStack()
      ->mkdir($test_project_dir)
      ->copy($this->bltRoot . '/subtree-splits/blt-project/composer.json', $test_project_dir . '/composer.json')
      ->run();

    $template_composer_json_filepath = $test_project_dir . '/composer.json';
    $template_composer_json = json_decode(file_get_contents($template_composer_json_filepath));
    $template_composer_json->repositories->blt = [
      'type' => 'path',
      'url' => '../blt',
      'options' => [
        'symlink' => TRUE,
      ],
    ];
    $template_composer_json->require->{'acquia/blt'} = '*@dev';

    file_put_contents($template_composer_json_filepath, json_encode($template_composer_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    $this->taskExecStack()
      ->dir($test_project_dir)
      ->exec("git init")
      ->exec("git add -A")
      ->exec("git commit -m \"Initial commit.\"")
      ->run();
    if (!$options['vm']) {
      $this->taskReplaceInFile($test_project_dir . "/composer.json")
        ->from("../blt")
        ->to($this->bltRoot)
        ->run();
    }
    $task = $this->taskExecStack()
      ->dir($test_project_dir)
      // BLT is the only dependency at this point. Install it.
      ->exec("composer install");

    if ($options['vm']) {
      $task->exec("$bin/blt vm --no-boot --no-interaction -v")
        ->exec("$bin/yaml-cli update:value box/config.yml vagrant_synced_folders.1.local_path '../blt'")
        ->exec("$bin/yaml-cli update:value box/config.yml vagrant_synced_folders.1.destination '/var/www/blt'")
        ->exec("$bin/yaml-cli update:value box/config.yml vagrant_synced_folders.1.type nfs");
    }
    $task->run();
  }

  /**
   * Create a new project using `composer create-project acquia/blt-project`.
   *
   * @option base-branch The blt-project (NOT blt) branch to test.
   * @option project-dir The directory in which the test project will be
   *   created.
   * @option vm Whether a VM will be booted.
   */
  public function createFromBltProject($options = [
    'base-branch' => self::BLT_DEV_BRANCH,
    'project-dir' => self::BLT_PROJECT_DIR,
  ]) {
    $test_project_dir = $this->bltRoot . "/" . $options['project-dir'];
    $this->prepareTestProjectDir($test_project_dir);
    $this->yell("Creating project from acquia/blt-project:{$options['base-branch']}-dev.");
    $return = $this->taskExecStack()
      ->dir($this->bltRoot . "/..")
      ->exec("COMPOSER_PROCESS_TIMEOUT=2000 composer create-project acquia/blt-project:{$options['base-branch']}-dev blted8 --no-interaction")
      ->run();

    return $return;
  }

  /**
   * Create a new project using `composer require acquia/blt`.
   *
   * @option base-branch The blt-project (NOT blt) branch to test.
   * @option project-dir The directory in which the test project will be
   *   created.
   * @option vm Whether a VM will be booted.
   */
  public function createFromScratch($options = [
    'base-branch' => self::BLT_DEV_BRANCH,
    'project-dir' => self::BLT_PROJECT_DIR,
    'vm' => TRUE,
  ]) {
    $test_project_dir = $this->bltRoot . "/" . $options['project-dir'];
    $bin = $test_project_dir . "/vendor/bin";
    $this->prepareTestProjectDir($test_project_dir);
    $this->taskFilesystemStack()->mkdir("$test_project_dir")->run();
    $this->taskExecStack()
      ->dir($test_project_dir)
      ->exec("composer init --name=acme/project --stability=dev --no-interaction")
      ->exec("composer config prefer-stable true")
      ->exec("git init")
      ->exec("git add -A")
      ->exec("git commit -m \"Initial commit.\"")
      ->run();
    $task = $this->taskExecStack()
      ->dir($test_project_dir)
      // BLT is the only dependency at this point. Install it.
      ->exec("composer require acquia/blt {$options['base-branch']}-dev");
    if ($options['vm']) {
      $task->exec("$bin/blt vm --no-boot --no-interaction -v");
    }
    $task->run();
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
   * @command subtree:push:blt-project
   *
   * @option branch (optional) The branch to push to. Defaults to current branch.
   *
   * @param array $options
   * @return void The CLI status code.
   *   The CLI status code.
   */
  public function subtreePushBltProject($options = [
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
   * @command subtree:push:blt-require-dev
   *
   * @option branch (optional) The branch to push to. Defaults to current branch.
   *
   * @param array $options
   * @return void The CLI status code.
   *   The CLI status code.
   */
  public function subtreePushBltRequireDev($options = [
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
    if (!empty($changes['breaking'])) {
      $text .= "**Major / breaking changes**\n\n";
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
   * @param $prev_tag
   *
   * @return array
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
   *
   * @return array
   *   A multidimensional array grouped by the labels enhancement and bug.
   */
  protected function sortChanges($log_entries, $github_token) {
    $client = new Client();
    $client->authenticate($github_token, NULL, Client::AUTH_URL_TOKEN);
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
   * @param $tag
   * @param $current_branch
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
   * @param $options
   * @param $current_branch
   *
   * @return mixed
   */
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
   * @param $uri
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
   * @param $current_branch
   */
  protected function resetLocalBranch($current_branch) {
    // Clean up all staged and unstaged files on current branch.
    $this->taskGitStack()
      ->exec('clean -fd .')
      ->exec('remote update')
      ->exec("reset --hard upstream/$current_branch")
      ->run();
  }

  /**
   * @param $test_project_dir
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  protected function prepareTestProjectDir($test_project_dir) {
    if (file_exists($test_project_dir . "/.vagrant")) {
      $this->taskExecStack()
        ->exec("vagrant destroy")
        ->dir($test_project_dir)
        ->run();
    }
    if (file_exists($test_project_dir)) {
      $this->logger->warning("This will destroy the $test_project_dir directory!");
      $continue = $this->confirm("Continue?");
      if (!$continue) {
        $this->say("Please run <comment>sudo rm -rf $test_project_dir</comment>");
        throw new BltException("$test_project_dir already exists.");
      }
    }
    $this->taskDeleteDir($test_project_dir)->run();
  }

  /**
   * @param $current_branch
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
