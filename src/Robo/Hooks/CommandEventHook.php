<?php

namespace Acquia\Blt\Robo\Hooks;

use Acquia\Blt\Robo\BltTasks;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\ArrayInput;

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
        $command_name = $event->getCommand()->getName();
        $options = $this->input->getOptions();
        $args = $this->input->getArguments();
        unset($args['command']);
        $command_definition = $command->getDefinition();
        $new_input = new ArrayInput(['blt', 'command' => $command_name], $command_definition);

        foreach ($args as $name => $value) {
          if ($command_definition->hasArgument($name)) {
            $new_input->setArgument($name, $value);
          }
        }

        foreach ($options as $name => $value) {
          if ($command_definition->hasOption($name)) {
            $new_input->setOption($name, $value);
          }
        }

        $new_input->setOption('define', 'drush.alias=self');

        $command_string = (string) $new_input;
        foreach ($new_input->getOptions() as $name => $value) {
          if ($new_input->getOption($name)) {
            if ($command_definition->getOption($name)->acceptValue()) {
              $command_string .= " --$name=$value";
            }
            else {
              $command_string .= " --$name";
            }
          }
        }

        // We cannot return an exit code directly, because disabled commands
        // always return ConsoleCommandEvent::RETURN_CODE_DISABLED.
        $result = $this->executeCommandInDrupalVm($command_string);
      }
    }

    // @todo Transmit analytics on command execution. Do the same in status hook.
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
