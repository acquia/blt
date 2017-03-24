<?php

namespace Acquia\Blt\Robo\Console;

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputDefinition;

/**
 * Class ConfigInput
 *
 * This class allows arbitrary configuration values to be passed on the
 * command line via `--property=value`.
 *
 * @package Acquia\Blt\Robo\Console
 */
class ConfigInput extends ArgvInput {

  /**
   * Re-declare $tokens and change visibility to protected.
   *
   * @var array
   */
  protected $tokens;

  /**
   * An array of configuration options passed on the command line.
   *
   * @var array
   */
  protected $configOptions;

  /**
   * {@inheritdoc}
   *
   * This is a direct copy of ArgvInput->construct(). We must copy it to gain
   * access to $this->tokens.
   */
  public function __construct(array $argv = null, InputDefinition $definition = null)
  {
    if (null === $argv) {
      $argv = $_SERVER['argv'];
    }

    // strip the application name
    array_shift($argv);

    $this->tokens = $argv;

    parent::__construct($definition);
  }

  /**
   * Get configuration options from the input.
   *
   * We assume that any long-form option for which no option definition exists
   * is a config option.
   */
  public function parseConfigOptions()
  {
    $args_and_options = $this->tokens;
    // Remove command name from token array.
    array_shift($args_and_options);

    $this->configOptions = [];
    foreach ($args_and_options as $key => $args_or_option) {
      if (0 === strpos($args_or_option, '--')) {
        if ($this->parseConfigOption($args_or_option)) {
          // If we found a config option, unset it from the input. Otherwise,
          // command will fail validation due to extra option being set.
          unset($this->tokens[$key]);
        }
      }
    }

    return $this->configOptions;
  }

  /**
   * Parses a single token into a config option.
   *
   * @param $token
   *
   * @return bool
   *   True if passed token was a parsable config option.
   */
  protected function parseConfigOption($token)
  {
    $key_val_string = substr($token, 2);
    if (false !== $pos = strpos($key_val_string, '=')) {
      $param_name = substr($key_val_string, 0, $pos);

      // If no option definition for this exists, we assume it is a config
      // option.
      if (!$this->hasOption($param_name)) {
        $value = substr($key_val_string, $pos + 1);
        $this->configOptions[$param_name] = $value;

        return TRUE;
      }

      return FALSE;
    }
  }


}
