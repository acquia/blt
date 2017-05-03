<?php

namespace Acquia\Blt\Robo\Commands\Frontend;

use Acquia\Blt\Robo\BltTasks;

/**
 * Defines commands in the "frontend:*" namespace.
 */
class FrontendCommand extends BltTasks {

  /**
   * Runs all frontend targets.
   *
   * @command frontend
   */
  public function frontend() {
    $status_code = $this->invokeCommands([
      'frontend:build',
      'frontend:setup',
      'frontend:test',
    ]);
    return $status_code;
  }

  /**
   * Indicates whether a frontend hook should be invoked inside of Drupal VM.
   *
   * @return bool
   *   TRUE if it should be invoked inside of  Drupal VM.
   */
  protected function shouldExecuteInDrupalVM() {
    return $this->getInspector()->isDrupalVmLocallyInitialized()
      && $this->getInspector()->isDrupalVmBooted()
      && !$this->getInspector()->isVmCli();
  }

  /**
   * Invokes a frontend hook.
   *
   * The hook will be invoked in Drupal VM if is initialized and booted.
   * Otherwise, it will be invoked on the host machine.
   *
   * @param string $hook
   *   The hook to invoke. E.g., "build" would invoke "frontend-build" hook.
   *
   * @return int|\Robo\Result
   *   The status code or result object.
   */
  protected function invokeFrontendHook($hook) {
    if ($this->shouldExecuteInDrupalVM()) {
      return $this->executeCommandInDrupalVm("blt frontend:$hook");
    }
    else {
      return $this->invokeHook("frontend-$hook");
    }
  }

  /**
   * Executes frontend-build target hook.
   *
   * @command frontend:build
   */
  public function build() {
    return $this->invokeFrontendHook('build');
  }

  /**
   * Executes frontend-setup target hook.
   *
   * @command frontend:setup
   */
  public function setup() {
    return $this->invokeFrontendHook('setup');
  }

  /**
   * Executes frontend-test target hook.
   *
   * @command frontend:test
   */
  public function test() {
    return $this->invokeFrontendHook('test');
  }

}
