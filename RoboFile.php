<?php

use Acquia\Blt\Robo\Exceptions\BltException;
use Github\Api\Issue;
use Robo\Contract\VerbosityThresholdInterface;
use Github\Client;
use Robo\Tasks;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Yaml\Yaml;

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

  const BLT_DEV_BRANCH = "9.1.x";

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
    'project-dir' => '../blted8',
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
    'project-dir' => '../blted8',
    'vm' => TRUE,
  ]) {
    $test_project_dir = $this->bltRoot . "/" . $options['project-dir'];
    $bin = $test_project_dir . "/vendor/bin";
    $this->prepareTestProjectDir($test_project_dir);
    $this->taskFilesystemStack()
      ->mkdir($test_project_dir)
      ->mirror($this->bltRoot . "/blted8", $test_project_dir)
      ->run();
    $this->taskExecStack()
      ->dir($test_project_dir)
      ->exec("git init")
      ->exec("git add -A")
      ->exec("git commit -m 'Initial commit.'")
      ->run();
    $this->taskReplaceInFile($test_project_dir . "/composer.json")
      ->from("../blt")
      ->to($this->bltRoot)
      ->run();
    $task = $this->taskExecStack()
      ->dir($test_project_dir)
      // BLT is the only dependency at this point. Install it.
      ->exec("composer install")
      // I have no idea why this is necessary, but testing on OSX does not pass
      // without it.
      ->exec("rm -rf $test_project_dir/vendor")
      ->exec("composer install");
    if ($options['vm']) {
      $task->exec("$bin/blt vm --no-boot --no-interaction --yes -v")
        ->exec("$bin/yaml-cli update:value box/config.yml vagrant_synced_folders.1.local_path '../blt'")
        ->exec("$bin/yaml-cli update:value box/config.yml vagrant_synced_folders.1.destination '/var/www/blt'")
        ->exec("$bin/yaml-cli update:value box/config.yml vagrant_synced_folders.1.type nfs");
    }
    $task->run();
  }

  /**
   * Create a new project using `composer create-project acquia/blt-project'.
   *
   * @option base-branch The blt-project (NOT blt) branch to test.
   * @option project-dir The directory in which the test project will be
   *   created.
   * @option vm Whether a VM will be booted.
   */
  public function createFromBltProject($options = [
    'base-branch' => self::BLT_DEV_BRANCH,
    'project-dir' => '../blted8',
  ]) {
    $test_project_dir = $this->bltRoot . "/" . $options['project-dir'];
    $this->prepareTestProjectDir($test_project_dir);
    $this->yell("Creating project from acquia/blt-project:{$options['base-branch']}-dev.");
    $this->taskExecStack()
      ->dir($this->bltRoot . "/..")
      ->exec("COMPOSER_PROCESS_TIMEOUT=2000 composer create-project acquia/blt-project:{$options['base-branch']}-dev blted8 --no-interaction")
      ->run();
  }

  /**
   * Create a new project using `composer require acquia/blt'.
   *
   * @option base-branch The blt-project (NOT blt) branch to test.
   * @option project-dir The directory in which the test project will be
   *   created.
   * @option vm Whether a VM will be booted.
   */
  public function createFromScratch($options = [
    'base-branch' => self::BLT_DEV_BRANCH,
    'project-dir' => '../blted8',
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
      ->exec("git commit -m 'Initial commit.'")
      ->run();
    $task = $this->taskExecStack()
      ->dir($test_project_dir)
      // BLT is the only dependency at this point. Install it.
      ->exec("composer require acquia/blt {$options['base-branch']}-dev");
    if ($options['vm']) {
      $task->exec("$bin/blt vm --no-boot --no-interaction --yes -v");
    }
    $task->run();
  }

  /**
   * Create a new project with one multisite.
   *
   * The project will be duplicated such that you may refer to the duplicate as
   * a remote instance of the site via a drush alias.
   *
   * This allows us to internally test syncing between multisite applications.
   *
   * @option project-dir The directory in which the test project will be
   *   created.
   * @option vm Whether a VM will be booted.
   */
  public function createMultisites($options = [
    'project-dir' => '../blted8',
    'vm' => FALSE,
  ]) {

    // Create test project.
    $options['project-type'] = 'symlink';
    $test_project_dir = $this->bltRoot . "/" . $options['project-dir'];
    $project_dir2 = $options['project-dir'] . "2";
    $test_project_dir2 = $this->bltRoot . "/" . $project_dir2;
    $this->prepareTestProjectDir($test_project_dir2);
    $this->createTestApp($options);

    // Create drush alias.
    $aliases = [
      'remote' => [
        'root' => $test_project_dir2,
      ],
    ];
    file_put_contents($test_project_dir . '/drush/sites/blted82.site.yml', Yaml::dump($aliases));

    // Generate multisite in test project.
    $bin = $test_project_dir . "/vendor/bin";
    $this->taskExecStack()
      ->dir($test_project_dir)
      ->exec("$bin/blt generate:multisite --site-name=site2 --yes --no-interaction")
      ->run();

    // Make a local clone of new project.
    $this->taskFilesystemStack()
      ->mirror(
        $test_project_dir,
        $test_project_dir2
      )
      ->run();

    // Generate sites.php for site1.
    $sites['local.blted8.site1.com'] = 'default';
    $sites['local.blted8.site2.com'] = 'site2';
    $contents = "<?php\n" . var_dump($sites);
    file_put_contents($test_project_dir . "/docroot/sites/sites.php", $contents);

    // Generate sites.php for site2.
    $sites['local.blted82.site1.com'] = 'default';
    $sites['local.blted82.site2.com'] = 'site2';
    $contents = "<?php\n" . var_dump($sites);
    file_put_contents($test_project_dir2 . "/docroot/sites/sites.php", $contents);

    $this->say("The following applications were created:");
    $this->say("* $test_project_dir");
    $this->say("  * site1 ");
    $this->say("      * dir: $test_project_dir/docroot/sites/default");
    $this->say("      * url: local.blted8.site1.com");
    $this->say("      * db config: $test_project_dir/docroot/sites/default/settings/local.settings.php");
    $this->say("  * site2 ");
    $this->say("      * dir: $test_project_dir/docroot/sites/site2");
    $this->say("      * url: local.blted8.site2.com");
    $this->say("      * db config: $test_project_dir/docroot/sites/site2/settings/local.settings.php");
    $this->say("* (pseudo remote) $test_project_dir2");
    $this->say("  * site1 ");
    $this->say("      * dir: $test_project_dir2/docroot/sites/default");
    $this->say("      * url: local.blted82.site1.com");
    $this->say("      * alias: @blted82.remote");
    $this->say("      * db config: $test_project_dir2/docroot/sites/default/settings/local.settings.php");
    $this->say("  * site2 ");
    $this->say("      * dir: $test_project_dir2/docroot/sites/site2");
    $this->say("      * url: local.blted82.site2.com");
    $this->say("      * alias: @blted82.remote --uri=site2");
    $this->say("      * db config: $test_project_dir2/docroot/sites/site2/settings/local.settings.php");
    $this->say("<comment>Please configure DB settings. You will need 4 databases.</comment>");
    $this->say("<comment>Please configure hosts settings. You will need 4 host entries.</comment>");
    $this->say("You may setup sites via:");
    $this->say("* cd $test_project_dir2");
    $this->say("* blt setup -D site=site1");
    $this->say("* blt setup -D site=site2");
    $this->say("* cd $test_project_dir");
    $this->say("* drush @blted82.remote status");
    $this->say("* drush @blted82.remote --uri=site2 status");
    $this->say("* blt sync:db:all");
  }

  /**
   * Executes pre-release tests against blt-project self::BLT_DEV_BRANCH.
   *
   * @option base-branch The blt-project (NOT blt) branch to test.
   * @option project-dir The directory in which the test project will be
   * @option environment The environment config var used for running Blt
   * commands against the test site.
   * @option create-project Whether to create a new blted8 site for testing.
   * @option project-dir The directory where the test site will be created.
   *   created.
   * @option project-type The type of test project that will be created. Valid
   *   values are standalone or symlink.
   * @option vm Whether a VM will be booted.
   */
  public function releaseTest($options = [
    'base-branch' => self::BLT_DEV_BRANCH,
    'environment' => 'ci',
    'create-project' => TRUE,
    'project-dir' => '../blted8',
    'project-type' => 'standalone',
    'vm' => TRUE,
  ]) {
    $this->stopOnFail();
    $use_vm = $options['vm'];
    $test_project_dir = $this->bltRoot . "/" . $options['project-dir'];
    if ($options['create-project']) {
      if ($options['project-type'] == 'symlink') {
        $this->createFromSymlink($options);
      }
      else {
        $this->createFromBltProject($options);
      }
    }
    $bin = $test_project_dir . "/vendor/bin";
    $blt_suffix = "--define environment={$options['environment']} --yes --no-interaction -vvv";
    $task = $this->taskExecStack()
      ->dir($test_project_dir)
      ->printMetadata(TRUE)
      ->exec("$bin/blt ci:travis:init $blt_suffix")
      ->exec("$bin/blt ci:pipelines:init $blt_suffix")
      ->exec("$bin/blt setup:cloud-hooks $blt_suffix")
      // Dump all config values to screen.
      ->exec("$bin/blt config:dump $blt_suffix")
      ->exec("$bin/blt acsf:init --yes")
      ->exec("{$this->bltRoot}/vendor/bin/robo sniff-code --load-from {$this->bltRoot}");
    if ($use_vm) {
      $task->exec("$bin/blt vm $blt_suffix");
      $drush_alias = '@blted8.local';
    }
    else {
      $drush_alias = '@self';
    }
    $task
      ->exec("$bin/blt validate $blt_suffix")
      // Ensure that at least one module gets enabled in CI env.
      ->exec("$bin/yaml-cli update:value blt/project.yml modules.ci.enable.0 views_ui")
      ->exec("$bin/yaml-cli update:value blt/project.yml cm.strategy none")
      // The tick-tock.sh script is used to prevent timeout.
      ->exec("{$this->bltRoot}/scripts/blt/ci/tick-tock.sh $bin/blt setup $blt_suffix")
      ->exec("$bin/blt tests {$blt_suffix}vv")
      ->exec("$bin/blt tests:behat:definitions $blt_suffix")

      // Test core-only config management.
      ->exec("$bin/drush $drush_alias config-export --root={$test_project_dir}/docroot --yes")
      ->exec("$bin/yaml-cli update:value blt/project.yml cm.strategy core-only")
      ->exec("$bin/blt setup:config-import $blt_suffix")

      // Test features config management.
      ->exec("$bin/yaml-cli update:value blt/project.yml cm.strategy features")
      ->exec("$bin/drush $drush_alias pm-enable features --root={$test_project_dir}/docroot --yes")
      ->exec("rm -rf {$test_project_dir}/config/default/*")
      ->exec("$bin/drush $drush_alias config-export --root={$test_project_dir}/docroot --yes")
      ->exec("$bin/blt setup:config-import $blt_suffix")
      ->exec("$bin/drush $drush_alias pm-uninstall features --root={$test_project_dir}/docroot --yes")

      // Test config split.
      ->exec("$bin/yaml-cli update:value blt/project.yml cm.strategy config-split")
      ->exec("$bin/drush $drush_alias pm-enable config_split --root={$test_project_dir}/docroot --yes")
      ->exec("rm -rf {$test_project_dir}/config/default/*")
      ->exec("$bin/drush $drush_alias config-export --root={$test_project_dir}/docroot --yes")
      ->exec("cp {$this->bltRoot}/scripts/blt/ci/internal/config_split.config_split.ci.yml {$test_project_dir}/config/default/")
      ->exec("$bin/blt setup:config-import $blt_suffix")
      ->exec("$bin/drush $drush_alias pm-uninstall config_split --root={$test_project_dir}/docroot --yes")
      ->exec("rm -rf {$test_project_dir}/config/default/*")

      // Test deploy.
      ->exec("$bin/blt deploy:update $blt_suffix")
      // Test SAML.
      ->exec("$bin/blt simplesamlphp:init $blt_suffix")
      ->exec("$bin/blt simplesamlphp:build:config $blt_suffix")
      // Test that custom commands are loaded.
      ->exec("$bin/blt custom:hello $blt_suffix")
      // Test the doctor.
      ->exec("$bin/blt doctor $blt_suffix")
      // Create a test multisite.
      ->exec("$bin/blt generate:multisite --site-name=site2 $blt_suffix");

    if (!$use_vm) {
      // Add Drupal VM config to repo without booting.
      $task->exec("$bin/blt vm --no-boot $blt_suffix");
    }

    $task->exec("$bin/blt deploy:build $blt_suffix");

    // Execute PHP Unit tests.
    $task->exec("$bin/phpunit {$this->bltRoot}/tests/phpunit --group blt -c {$this->bltRoot}/tests/phpunit/phpunit.xml -v")
      ->exec("$bin/phpunit {$this->bltRoot}/tests/phpunit --group blted8 -c {$this->bltRoot}/tests/phpunit/phpunit.xml -v")
      ->run();
    $this->say("<info>Completed testing.</info>");
    if ($use_vm) {
      $continue = $this->confirm("Destroy VM?");
      if (!$continue) {
        // Not really a failure.
        return 0;
      }
      $this->taskExecStack()
        ->dir($test_project_dir)
        ->exec("$bin/blt vm:nuke $blt_suffix")
        ->run();
    }
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
      ->regex('/(const VERSION = \')([0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,2})(\';)/')
      ->to('${1}' . $tag . '${3}')
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
    $branch_matches_upstream = $this->_exec("git diff $current_branch origin/$current_branch --quiet")->wasSuccessful();
    if (!$branch_matches_upstream) {
      $this->logger->warning("$current_branch does not match origin/$current_branch.");
      $this->logger->warning("Continuing will cause you to lose all local changes!");
      $continue = $this->confirm("Continue?");
      if (!$continue) {
        throw new BltException("Release terminated by user.");
      }
    }
  }

}
