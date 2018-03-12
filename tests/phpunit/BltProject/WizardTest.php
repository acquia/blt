<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Tests\BltProjectTestBase;
use Symfony\Component\Yaml\Yaml;

/**
 * Class WizardTest.
 */
class WizardTest extends BltProjectTestBase {

  /**
   * Tests wizard with recipe file option
   */
  public function testWizardUsingRecipe() {
    $recipe_filepath = $this->bltDirectory . '/tests/phpunit/fixtures/recipe.yml';
    $this->blt("wizard", [
      '--recipe' => $recipe_filepath,
      '--yes' => '',
    ]);

    $recipe = Yaml::parseFile($recipe_filepath);
    $project_configuration = Yaml::parseFile($this->sandboxInstance . '/blt/blt.yml');

    $this->assertEquals($recipe['human_name'], $project_configuration['project']['human_name']);
    $this->assertEquals($recipe['machine_name'], $project_configuration['project']['machine_name']);
    $this->assertEquals($recipe['prefix'], $project_configuration['project']['prefix']);
    $this->assertEquals($recipe['human_name'], $project_configuration['project']['human_name']);
    $this->assertFileExists($this->bltDirectory . '/acquia-pipelines.yml');
    $this->assertFileNotExists($this->bltDirectory . '/Vagrantfile');
  }

}
