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
   *
   * @executeInDrupalVm
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
   * Invokes a frontend hook.
   *
   * @param string $hook
   *   The hook to invoke. E.g., "build" would invoke "frontend-build" hook.
   *
   * @return int|\Robo\Result
   *   The status code or result object.
   */
  protected function invokeFrontendHook($hook) {
    return $this->invokeHook("frontend-$hook");
  }

  /**
   * Executes frontend-build target hook.
   *
   * @command frontend:build
   *
   * @executeInDrupalVm
   */
  public function build() {
    return $this->invokeFrontendHook('build');
  }

  /**
   * Executes frontend-setup target hook.
   *
   * @command frontend:setup
   *
   * @executeInDrupalVm
   */
  public function setup() {
    return $this->invokeFrontendHook('setup');
  }

  /**
   * Executes frontend-test target hook.
   *
   * @command frontend:test
   *
   * @executeInDrupalVm
   */
  public function test() {
    return $this->invokeFrontendHook('test');
  }

}
