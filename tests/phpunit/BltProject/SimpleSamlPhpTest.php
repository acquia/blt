<?php

namespace Acquia\Blt\Tests\BltProject;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class SimpleSamlPhpTest.
 *
 * Verifies simplesamlphp configuration.
 */
class SimpleSamlPhpTest extends BltProjectTestBase {

  /**
   * Tests simplesamlphp:config:init target.
   *
   * Ensures simplesamlphp config files were copied to project.
   *
   * @group blt-project
   */
  public function testSimpleSamlPhpConfigInit() {
    $simpleSamlPhpConfigDirectory = "{$this->projectDirectory}/simplesamlphp/config";
    $simpleSamlPhpMetadataDirectory = "{$this->projectDirectory}/simplesamlphp/metadata";

    $this->assertFileExists("${simpleSamlPhpConfigDirectory}/authsources.php");
    $this->assertFileExists("${simpleSamlPhpConfigDirectory}/config.php");
    $this->assertFileExists("${simpleSamlPhpConfigDirectory}/acquia_config.php");

    $configFilePath = "${simpleSamlPhpConfigDirectory}/config.php";
    if (file_exists($configFilePath)) {
      $configFile = file_get_contents($configFilePath);
      $this->assertContains("include 'acquia_config.php';", $configFile);
    }

    $this->assertFileExists("${simpleSamlPhpMetadataDirectory}/saml20-idp-remote.php");
  }

  /**
   * Tests setSimpleSamlPhpInstalled.
   *
   * Ensures project.yml was updated with simplesamlphp key.
   *
   * @group blt-project
   */
  public function testSetSimpleSamlPhpInstalled() {
    $this->assertArrayHasKey('simplesamlphp', $this->config);
  }

  /**
   * Tests symlinkDocrootToLibDir.
   *
   * Ensures a symlink from the docroot to web accessible lib dir was created.
   *
   * @group blt-project
   */
  public function testSymlinkDocrootToLibDir() {
    $this->assertFileExists("{$this->drupalRoot}/simplesaml/saml2");
  }

}
