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
   * @executeInDrupalVm
   */
  public function build() {
    return $this->invokeHook('source:build:frontend-assets');
  }

  /**
   * Executes source:build:frontend-reqs target hook.
   *
   * @command source:build:frontend-reqs
   * @executeInDrupalVm
   */
  public function setup() {
    return $this->invokeHook('source:build:frontend-reqs');
  }

  /**
   * Executes frontend-test target hook.
   *
   * @command frontend:test
   *
   * @launchWebServer
   * @executeInDrupalVm
   */
  public function test() {
    return $this->invokeHook('frontend-test');
  }

}
