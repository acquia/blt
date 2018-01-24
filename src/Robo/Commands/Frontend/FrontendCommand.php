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
   * @command source:build:frontend
   * @aliases sbf frontend
   * @executeInDrupalVm
   */
  public function frontend() {
    $this->invokeCommands([
      'source:build:frontend-reqs',
      'source:build:frontend-assets',
    ]);
  }

  /**
   * Executes source:build:frontend-assets target hook.
   *
   * @command source:build:frontend-assets
   * @aliases sbfa frontend:build
   * @executeInDrupalVm
   */
  public function assets() {
    return $this->invokeHook('frontend-assets');
  }

  /**
   * Executes source:build:frontend-reqs target hook.
   *
   * @command source:build:frontend-reqs
   * @aliases sbfr frontend:setup
   * @executeInDrupalVm
   */
  public function reqs() {
    return $this->invokeHook('frontend-reqs');
  }

  /**
   * Executes frontend-test target hook.
   *
   * @command tests:frontend:run
   * @aliases tfr tests:frontend frontend:test
   *
   * @todo add alias for tests:frontend.
   *
   * @launchWebServer
   * @executeInDrupalVm
   */
  public function test() {
    return $this->invokeHook('frontend-test');
  }

}
