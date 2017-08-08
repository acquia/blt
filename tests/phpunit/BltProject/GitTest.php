<?php

namespace Acquia\Blt\Tests\BltProject;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class GitTasksTest.
 *
 * Verifies that git related tasks work as expected.
 */
class GitTasksTest extends BltProjectTestBase {

  /**
   * Tests  setup:git-hooks command.
   *
   * @group blt-project
   */
  public function testGitConfig() {
    $this->assertFileExists($this->projectDirectory . '/.git');
    $this->assertFileExists($this->projectDirectory . '/.git/hooks/commit-msg');
    $this->assertFileExists($this->projectDirectory . '/.git/hooks/pre-commit');
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
   *
   * @group blt-project
   */
  public function testGitHookCommitMsg($is_valid, $commit_message, $message = NULL) {
    $this->assertCommitMessageValidity($is_valid, $commit_message, $message);
  }

  /**
   * Data provider.
   */
  public function providerTestGitHookCommitMsg() {
    $prefix = isset($this->config['project']) ? $this->config['project']['prefix'] : '';
    return array(
      array(FALSE, "This is a bad commit.", 'Missing prefix and ticket number.'),
      array(FALSE, "123: This is a bad commit.", 'Missing project prefix.'),
      array(FALSE, "{$prefix}: This is a bad commit.", 'Missing ticket number.'),
      array(FALSE, "{$prefix}-123 This is a bad commit.", 'Missing colon.'),
      array(FALSE, "{$prefix}-123: This is a bad commit", 'Missing period.'),
      array(FALSE, "{$prefix}-123: Hello.", 'Too short.'),
      array(FALSE, "NOT-123: This is a bad commit.", 'Wrong project prefix.'),
      array(TRUE, "{$prefix}-123: This is a good commit.", 'Good commit.'),
      array(TRUE, "{$prefix}-123: This is an exceptionally long--seriously, really, really, REALLY long, but still good commit.", 'Long good commit.',
      ),
    );
  }

  /**
   * Tests operation of scripts/git-hooks/pre-commit.
   *
   * Should assert that code validation via phpcs is functioning.
   *
   * @group blt-project
   */
  public function testGitPreCommitHook() {
    // Commits must be executed inside of new project directory.
    chdir($this->projectDirectory);
    $prefix = $this->config['project']['prefix'];
    $command = "./.git/hooks/pre-commit";
    $output = shell_exec($command);
    $this->assertContains('validate:phpcs', $output);
    $this->assertContains('validate:yaml:files', $output);
    $this->assertContains('validate:twig:files', $output);
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
    // Commits must be executed inside of new project directory.
    chdir($this->projectDirectory);

    // "2>&1" redirects standard error output to standard output.
    $command = "mkdir -p {$this->projectDirectory}/tmp && echo '$commit_message' > {$this->projectDirectory}/tmp/blt_commit_msg && {$this->projectDirectory}/.git/hooks/commit-msg {$this->projectDirectory}/tmp/blt_commit_msg 2>&1";

    exec($command, $output, $return);
    $this->assertNotSame($is_valid, (bool) $return, $message);
  }

}
