<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltProjectTestBase;
use Symfony\Component\Yaml\Yaml;
use Acquia\Blt\Robo\Config\ConfigAwareTrait;

/**
 * Class BltConfigurationWizard.
 */
class BltConfigurationWizard extends BltProjectTestBase {

  use ConfigAwareTrait;

  /**
   * Tests setup:wizard command.
   */
  public function testWizard() {
    $this->markTestIncomplete(
      'This test has not been implemented yet.'
    );
  }

  /**
   * Tests setup:wizard with recipe file option
   */
  public function testWizardUsingRecipe() {
    $this->blt("setup:wizard", ['--recipe' => 'recipe.yml']);

    $recipe = Yaml::parse(
      file_get_contents('recipe.yml')
    );

    /*
     * TODO: Determine if there is a better way to test this.
     *
     * The project.yml file seems to be missing most of the values you'd expect.
     * Is the setup for the PHPUnit tests not copying over the default file as
     * you would expect?
     */
    $project_configuration = Yaml::parse(
      file_get_contents('blt/project.yml')
    );

    $config_keys = [
      'human_name',
      'machine_name',
      'prefix',
      'vm',
    ];

    foreach($config_keys as $key) {
      $this->assertEquals($recipe[$key], $project_configuration['project'][$key]);
    }

  }

}
