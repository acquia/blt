<?php

namespace Acquia\Blt\Robo\Commands\Frontend;

use Acquia\Blt\Robo\BltTasks;

/**
 * Defines commands in the "frontend:*" namespace.
 */
class FrontendCommand extends BltTasks {

  /**
   * Runs all frontend targets
   *
   * @command frontend
   */
  public function frontend() {
    $this->say("Running frontend tasks...");
    $status_code = $this->invokeCommands([
      'frontend:build',
      'frontend:setup',
      'frontend:test',
    ]);
    return $status_code;
  }

  /**
   * Uses project.yml hooks to run custom defined commands to
   * build front end dependencies for custom themes.
   *
   * @command frontend:build
   */
  public function build() {
    $this->invokeHook('frontend-build');
  }

  /**
   * Uses project.yml hooks to run custom defined commands to
   * setup front end dependencies for frontend:build.
   *
   * @command frontend:setup
   */
  public function setup() {
    $this->invokeHook('frontend-setup');
  }

  /**
   * Uses project.yml hooks to run tests for the frontend as
   *
   * @command frontend:test
   */
  public function test() {
    $this->invokeHook('frontend-test');
  }

}
