<?php

namespace Acquia\Blt\Robo\Tasks;

/**
 * Load BLT's custom Robo tasks.
 */
trait LoadTasks {

  /**
   * @return \Acquia\Blt\Robo\Tasks\DrushTask
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
   * @param null|string $pathToPhpUnit
   *
   * @return \Acquia\Blt\Robo\Tasks\PhpUnitTask
   */
  protected function taskPHPUnitTask($pathToPhpUnit = null) {
    return $this->task(PhpUnitTask::class, $pathToPhpUnit);
  }

  /**
   * @return \Acquia\Blt\Robo\Tasks\RunTestsTask
   */
  protected function taskRunTestsTask($runTestsScriptCommand = null) {
    return $this->task(RunTestsTask::class, $runTestsScriptCommand);
  }

}
