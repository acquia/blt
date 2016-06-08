<?php

namespace Drupal\Tests\PHPUnit;

/**
 * Class GitTasksTest.
 *
 * Verifies that git related tasks work as expected.
 */
class GitTasksTest extends TestBase {

  /**
   * Tests Phing setup:git-hooks target.
   */
  public function testGitConfig() {
    $this->assertFileExists($this->projectDirectory . '/.git');
    $this->assertFileExists($this->projectDirectory . '/.git/hooks/commit-msg');
    $this->assertFileExists($this->projectDirectory . '/.git/hooks/pre-commit');
    $this->assertNotContains(
      '${project.prefix}',
      file_get_contents($this->projectDirectory . '/.git/hooks/commit-msg')
    );
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
    $this->assertCommitMessageValidity($is_valid, $commit_message, $message);
  }

  /**
   * Data provider.
   */
  public function providerTestGitHookCommitMsg() {
    $prefix = $this->config['project']['prefix'];
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
   */
  public function testGitPreCommitHook() {
    // Commits must be executed inside of new project directory.
    chdir($this->projectDirectory);
    $prefix = $this->config['project']['prefix'];
    $command = "git commit --amend -m '$prefix-123: This is a good commit.' 2>&1";
    $output = shell_exec($command);
    $this->assertNotContains('PHP Code Sniffer was not found', $output);
    $this->assertContains('Sniffing staged files via PHP Code Sniffer.', $output);
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
    $command = "git commit --amend -m '$commit_message' 2>&1";
    print "Executing \"$command\" \n";

    $output = shell_exec($command);
    $invalid_commit_text = 'Invalid commit message';
    $output_contains_invalid_commit_text = (bool) strstr($output, $invalid_commit_text);
    $this->assertNotSame($is_valid, $output_contains_invalid_commit_text, $message);
  }

}
