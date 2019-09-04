<?php

namespace Acquia\Blt\Robo\Tasks;

use Robo\LoadAllTasks;

/**
 * Load BLT's custom Robo tasks.
 */
trait LoadTasks {

  use LoadAllTasks;

  /**
   * Task drush.
   *
   * @return \Acquia\Blt\Robo\Tasks\DrushTask
   *   Drush task.
   */
  protected function taskDrush() {
    /** @var \Acquia\Blt\Robo\Tasks\DrushTask $task */
    $task = $this->task(DrushTask::class);
    $task->setInput($this->input());
    /** @var \Symfony\Component\Console\Output\OutputInterface $output */
    $output = $this->output();
    $task->setVerbosityThreshold($output->getVerbosity());

    return $task;
  }

  /**
   * Task git.
   *
   * @param null|string $pathToGit
   *   Path to git.
   *
   * @return \Acquia\Blt\Robo\Tasks\GitTask|\Robo\Collection\CollectionBuilder
   *   Git task.
   */
  protected function taskGit($pathToGit = 'git') {
    return $this->task(GitTask::class, $pathToGit);
  }

  /**
   * Task phpunit.
   *
   * @param null|string $pathToPhpUnit
   *   Path to phpunit.
   *
   * @return \Acquia\Blt\Robo\Tasks\PhpUnitTask|\Robo\Collection\CollectionBuilder
   *   Phpunit task.
   */
  protected function taskPhpUnitTask($pathToPhpUnit = NULL) {
    return $this->task(PhpUnitTask::class, $pathToPhpUnit);
  }

  /**
   * Task run tests.
   *
   * @return \Acquia\Blt\Robo\Tasks\RunTestsTask|\Robo\Collection\CollectionBuilder
   *   run tests task.
   */
  protected function taskRunTestsTask($runTestsScriptCommand = NULL) {
    return $this->task(RunTestsTask::class, $runTestsScriptCommand);
  }

  /**
   * Use BLT's implementation of the Robo ExecStack.
   *
   * @inheritDoc
   */
  protected function taskExecStack() {
    return $this->task(BltExecStack::class);
  }

  /**
   * Use BLT's implementation of the Robo Exec.
   *
   * @inheritDoc
   */
  protected function taskExec($command) {
    return $this->task(BltExec::class, $command);
  }

  /**
   * Use BLT's implementation of the Robo Write.
   *
   * @inheritDoc
   */
  protected function taskWriteToFile($file) {
    return $this->task(BltWrite::class, $file);
  }

}
