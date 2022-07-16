<?php

namespace Acquia\Blt\Tests;

use Acquia\Blt\Robo\Commands\Drupal\ConfigCommand;
use Acquia\Blt\Robo\Inspector\Inspector;
use Acquia\Blt\Robo\Tasks\DrushTask;
use Robo\Result;

/**
 * Test blt config imports.
 */
class ConfigImportTest extends BltProjectTestBase {

  /**
   * @throws \Exception
   */
  public function testNoConfig() {
    $this->executor->drush(["config-export", "--yes"]);
    [$status_code] = $this->blt("drupal:config:import", [
      '--define' => [
        'cm.strategy=none',
      ],
    ]);
    $this::assertEquals(0, $status_code);
  }

  /**
   * @throws \Exception
   */
  public function testNoConfigException() {
    $this->executor->drush(["config-export", "--yes"]);
    try {
      [$status_code] = $this->blt("drupal:config:import", [
        '--define' => [
          'cm.strategy=no',
        ],
      ]);
      $this::assertEquals(0, $status_code);
    }
    catch (\Exception $e) {
      throw new \Exception("Command exited with non-zero exit code.");
    }
  }

  /**
   * @throws \Exception
   */
  public function testUnSuccessCase() {
    $this->expectException('Exception');
    $mockconfigcommand = $this->getMockBuilderObj(ConfigCommand::class, [
      'getConfigValue',
      'getInspector',
      'taskDrush',
    ]);
    $mockinspector = $this->getMockBuilderObj(Inspector::class, [
      'isActiveConfigIdentical',
    ]);
    $mockinspector->expects($this->any())->method('isActiveConfigIdentical')->willReturn(FALSE);
    $mockdrushtask = $this->getMockBuilderObj(DrushTask::class, [
      'stopOnFail',
      'drush',
      'run',
    ]);
    $mockdrushtask->expects($this->any())->method('stopOnFail')->willReturn($mockdrushtask);
    $mockdrushtask->expects($this->any())->method('drush')->willReturn($mockdrushtask);
    $mockresult = $this->getMockBuilder(Result::class)
      ->disableOriginalConstructor()
      ->onlyMethods([
        'wasSuccessful',
      ])
      ->getMock();
    $mockresult->expects($this->any())->method('wasSuccessful')->willReturn(TRUE);
    $mockdrushtask->expects($this->any())->method('run')->willReturn($mockresult);
    $mockconfigcommand->expects($this->any())->method('getInspector')->willReturn($mockinspector);
    $mockconfigcommand->expects($this->any())->method('getConfigValue')->willReturn(NULL);
    $mockconfigcommand->expects($this->any())->method('taskDrush')->willReturn($mockdrushtask);
    $testMethod = new \ReflectionMethod(
      ConfigCommand::class,
      'checkConfigOverrides'
    );
    $testMethod->setAccessible(TRUE);
    $testMethod->invoke($mockconfigcommand);
  }

  /**
   * @throws \Exception
   */
  public function testCoreOnly() {
    $this->executor->drush(["config-export", "--yes"]);
    [$status_code] = $this->blt("drupal:config:import", [
      '--define' => [
        'cm.strategy=core-only',
      ],
    ]);
    static::assertEquals(0, $status_code);
  }

  /**
   * @todo re-enable after Config Split D9 release.
   *
   * @group orca_ignore
   *
   * @throws \Exception
   */
  public function testConfigSplit() {
    $this->executor->drush(["pm-enable config_split", "--yes"]);
    $this->executor->drush(["config-export", "--yes"]);
    $this->fs->copy(
      $this->bltDirectory . "/scripts/blt/ci/internal/config_split.config_split.ci.yml",
      $this->sandboxInstance . "/config/default/config_split.config_split.ci.yml"
    );
    [$status_code] = $this->blt("drupal:config:import", [
      '--define' => [
        'cm.strategy=config-split',
      ],
    ]);
    static::assertEquals(0, $status_code);
  }

  /**
   * @throws \Exception
   */
  public function testUpdateFailed() {
    $this->expectException('Exception');
    $mockconfigcommand = $this->getMockBuilderObj(ConfigCommand::class, [
      'taskDrush',
    ]);
    $mockdrushtask = $this->getMockBuilderObj(DrushTask::class, [
      'stopOnFail',
      'drush',
      'run',
    ]);
    $mockdrushtask->expects($this->any())->method('stopOnFail')->willReturn($mockdrushtask);
    $mockdrushtask->expects($this->any())->method('drush')->willReturn($mockdrushtask);
    $mockresult = $this->getMockBuilderObj(Result::class, [
      'wasSuccessful',
    ]);
    $mockresult->expects($this->any())->method('wasSuccessful')->willReturn(FALSE);
    $mockdrushtask->expects($this->any())->method('run')->willReturn($mockresult);
    $mockconfigcommand->expects($this->any())->method('taskDrush')->willReturn($mockdrushtask);
    $mockconfigcommand->update();
  }

  /**
   * @throws \Exception
   */
  public function testImportConfigSplit() {
    $mockconfigcommand = $this->getMockBuilderObj(ConfigCommand::class, []);
    $mockdrushtask = $this->getMockBuilderObj(DrushTask::class, [
      'drush',
    ]);
    $mockdrushtask->expects($this->any())->method('drush')->willReturn($mockdrushtask);
    $testImportConfigSplitMethod = new \ReflectionMethod(
      ConfigCommand::class,
      'importConfigSplit'
    );
    $testImportConfigSplitMethod->setAccessible(TRUE);
    $this->assertNull($testImportConfigSplitMethod->invokeArgs($mockconfigcommand, [$mockdrushtask]));
  }

  /**
   * @throws \Exception
   */
  public function testExportedSiteUuidNull() {
    $mockconfigcommand = $this->getMockBuilderObj(ConfigCommand::class, [
      'getConfigValue',
    ]);
    $mockconfigcommand->expects($this->any())->method('getConfigValue')->willReturn('invalid_path');
    $testExportedSiteUuidMethod = new \ReflectionMethod(
      ConfigCommand::class,
      'getExportedSiteUuid'
    );
    $testExportedSiteUuidMethod->setAccessible(TRUE);
    $this->assertNull($testExportedSiteUuidMethod->invokeArgs($mockconfigcommand, ['invalid_path']));
  }

  /**
   * @throws \Exception
   */
  public function testUnSuccessCaseException() {
    $this->expectException('Exception');
    $mockconfigcommand = $this->getMockBuilderObj(ConfigCommand::class, [
      'getConfigValue',
      'getInspector',
      'taskDrush',
    ]);
    $mockinspector = $this->getMockBuilderObj(Inspector::class, [
      'isActiveConfigIdentical',
    ]);
    $mockinspector->expects($this->any())->method('isActiveConfigIdentical')->willReturn(FALSE);
    $mockdrushtask = $this->getMockBuilderObj(DrushTask::class, [
      'stopOnFail',
      'drush',
      'run',
    ]);
    $mockdrushtask->expects($this->any())->method('stopOnFail')->willReturn($mockdrushtask);
    $mockdrushtask->expects($this->any())->method('drush')->willReturn($mockdrushtask);
    $mockresult = $this->getMockBuilderObj(Result::class, [
      'wasSuccessful',
    ]);
    $mockresult->expects($this->any())->method('wasSuccessful')->willReturn(FALSE);
    $mockdrushtask->expects($this->any())->method('run')->willReturn($mockresult);
    $mockconfigcommand->expects($this->any())->method('getInspector')->willReturn($mockinspector);
    $mockconfigcommand->expects($this->any())->method('getConfigValue')->willReturn(NULL);
    $mockconfigcommand->expects($this->any())->method('taskDrush')->willReturn($mockdrushtask);
    $testMethod = new \ReflectionMethod(
      ConfigCommand::class,
      'checkConfigOverrides'
    );
    $testMethod->setAccessible(TRUE);
    $testMethod->invoke($mockconfigcommand);
  }

  /**
   * Mock object of drupal config command.
   *
   * @param mixed $class
   *   Class name of mock obj.
   * @param array $methods
   *   Name of methods.
   *
   * @return mixed
   *   Return mock object of drupal config command.
   */
  public function getMockBuilderObj($class, array $methods) {
    $mockconfigcommand = $this->getMockBuilder($class)
      ->disableOriginalConstructor();
    if (!empty($methods)) {
      $mockconfigcommand->onlyMethods($methods);
    }
    return $mockconfigcommand->getMock();
  }

}
