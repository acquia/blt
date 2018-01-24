<?php

namespace Acquia\Blt\Tests\BltProject;

use Acquia\Blt\Tests\BltProjectTestBase;
use Symfony\Component\Process\Process;

/**
 * Class GitTasksTest.
 */
class GitTasksTest extends BltProjectTestBase {

  /**
   * Tests blt:init:git-hooks command.
   */
  public function testGitConfig() {
    $this->blt("blt:init:git-hooks");
    $this->assertFileExists($this->sandboxInstance . '/.git');
    $this->assertFileExists($this->sandboxInstance . '/.git/hooks/commit-msg');
    $this->assertFileExists($this->sandboxInstance . '/.git/hooks/pre-commit');
  }

  /**
   * Tests operation of scripts/git-hooks/commit-msg.
   *
   * @param bool $is_valid
   *   Whether the message is valid.
   * @param string $commit_message
   *   The git commit message.
   * @param string $message
   *   The PHPUnit message to be output for this datapoint.
   *
   * @dataProvider providerTestGitHookCommitMsg
   */
  public function testGitHookCommitMsg($is_valid, $commit_message, $message = NULL) {
    $prefix = $this->config->get('project.prefix');
    $commit_message = str_replace('[prefix]', $prefix, $commit_message);
    $this->assertCommitMessageValidity($is_valid, $commit_message, $message);
  }

  /**
   * Data provider.
   */
  public function providerTestGitHookCommitMsg() {
    return array(
      array(FALSE, "This is a bad commit.", 'Missing prefix and ticket number.'),
      array(FALSE, "123: This is a bad commit.", 'Missing project prefix.'),
      array(FALSE, "[prefix]: This is a bad commit.", 'Missing ticket number.'),
      array(FALSE, "[prefix]-123 This is a bad commit.", 'Missing colon.'),
      array(FALSE, "[prefix]-123: This is a bad commit", 'Missing period.'),
      array(FALSE, "[prefix]-123: Hello.", 'Too short.'),
      array(FALSE, "NOT-123: This is a bad commit.", 'Wrong project prefix.'),
      array(TRUE, "Merge branch 'feature/test'", 'Good commit.'),
      array(TRUE, "[prefix]-123: This is a good commit.", 'Good commit.'),
      array(TRUE, "[prefix]-123: This is an exceptionally long--seriously, really, really, REALLY long, but still good commit.", 'Long good commit.',
      ),
    );
  }

  /**
   * Tests operation of scripts/git-hooks/pre-commit.
   *
   * Should assert that code validation via phpcs is functioning.
   */
  public function testGitPreCommitHook() {
    $this->blt("blt:init:git-hooks");
    // Commits must be executed inside of new project directory.
    $process = new Process("./.git/hooks/pre-commit", $this->sandboxInstance);
    $process->run();
    $output = $process->getOutput();
    // @todo Assert only changed files are validated.
    $this->assertContains('tests:phpcs:sniff:files', $output);
    $this->assertContains('tests:yaml:lint:files', $output);
    $this->assertContains('tests:twig:lint:files', $output);
  }

  /**
   * Asserts that a given commit message is valid or not.
   *
   * @param bool $is_valid
   *   Whether the message is valid.
   * @param string $commit_message
   *   The git commit message.
   * @param string $message
   *   The PHPUnit message to be output for this datapoint.
   */
  protected function assertCommitMessageValidity($is_valid, $commit_message, $message = '') {
    // "2>&1" redirects standard error output to standard output.
    $command = "mkdir -p {$this->sandboxInstance}/tmp && echo '$commit_message' > {$this->sandboxInstance}/tmp/blt_commit_msg && {$this->sandboxInstance}/.git/hooks/commit-msg {$this->sandboxInstance}/tmp/blt_commit_msg 2>&1";

    $process = new Process($command, $this->sandboxInstance);
    $process->run();
    $valid_word = $is_valid ? 'valid' : 'invalid';
    $this->assertNotSame($is_valid, $process->getExitCode(), "Failed asserting that commit message \"$commit_message\" is $valid_word. Testing purpose was: $message");
  }

}
