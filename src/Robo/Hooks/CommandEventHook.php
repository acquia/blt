<?php

namespace Acquia\Blt\Robo\Hooks;

use Acquia\Blt\Robo\BltTasks;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

/**
 * This class defines hooks that provide user interaction.
 *
 * These hooks typically use a Wizard to evaluate the validity of config or
 * state and guide the user toward resolving issues.
 */
class CommandEventHook extends BltTasks {

  /**
   * Disable any command listed in the `disable-target` config key.
   *
   * @hook command-event *
   */
  public function skipDisabledCommands(ConsoleCommandEvent $event) {
    $command = $event->getCommand();
    if ($this->isCommandDisabled($command->getName())) {
      $event->disableCommand();
    }
  }

  /**
   * Issues warnings to user if their local environment is mis-configured.
   *
   * @hook command-event *
   */
  public function issueWarnings(ConsoleCommandEvent $event) {
    // The inspector tracks whether warnings have been issued because it is
    // shared in the container.
    $this->getInspector()->issueEnvironmentWarnings();
  }

  /**
   * Execute a command inside of Drupal VM.
   *
   * @hook command-event *
   */
  public function executeInDrupalVm(ConsoleCommandEvent $event) {
    // @todo Create global option to opt-out of this. E.g., --execute-on-host.
    $command = $event->getCommand();
    if (method_exists($command, 'getAnnotationData')) {
      /* @var \Consolidation\AnnotatedCommand\AnnotationData */
      $annotation_data = $event->getCommand()->getAnnotationData();
      if ($annotation_data->has('executeInDrupalVm') && $this->shouldExecuteInDrupalVm()) {
        $event->disableCommand();
        $args = $this->getCliArgs();
        $command_name = $event->getCommand()->getName();

        $command_parts = [];
        $command_parts[] = "blt $command_name";
        if (!empty($args)) {
          $command_parts[] = $args;
        }
        $command_parts[] = "--define drush.alias=self";
        $full_command = implode(' ', $command_parts);

        // We cannot return an exit code directly, because disabled commands
        // always return ConsoleCommandEvent::RETURN_CODE_DISABLED.
        $result = $this->executeCommandInDrupalVm($full_command);
      }
    }

    // @todo Transmit analytics on command execution. Do the same in status hook.
  }

  /**
   * Gets the CLI args that were used when BLT was executed.
   *
   * This does not include 'blt' or the command name like 'test:behat'. Example
   * value: ['-v', ['--key="value"']].
   *
   * @return array|string
   *   The CLI args.
   */
  protected function getCliArgs() {
    $args = array_slice($_SERVER['argv'], 2);
    $args = array_map([$this, 'escapeShellArg'], $args);
    $args = implode(' ', $args);

    return $args;
  }

  /**
   * Escapes the value for CLI arguments in form key=value.
   *
   * @param string $arg
   *   The argument to be escaped.
   *
   * @return string
   *   The escaped argument.
   */
  protected function escapeShellArg($arg) {
    if (strpos($arg, '-') !== 0
      && strstr($arg, '=') !== FALSE) {
      $arg_parts = explode('=', $arg);
      $arg = $arg_parts[0] . '="' . $arg_parts[1] . '"';
    }

    return $arg;
  }

  /**
   * Indicates whether a frontend hook should be invoked inside of Drupal VM.
   *
   * @return bool
   *   TRUE if it should be invoked inside of  Drupal VM.
   */
  protected function shouldExecuteInDrupalVm() {
    return !$this->getInspector()->isVmCli() &&
      $this->getInspector()->isDrupalVmLocallyInitialized()
      && $this->getInspector()->isDrupalVmBooted();
  }

}
