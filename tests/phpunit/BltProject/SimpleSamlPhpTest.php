<?php

namespace Acquia\Blt\Tests\BltProject;

use Acquia\Blt\Tests\BltProjectTestBase;

/**
 * Class SimpleSamlPhpTest.
 */
class SimpleSamlPhpTest extends BltProjectTestBase {

  /**
   * Tests simplesamlphp:init target.
   *
   * Ensures simplesamlphp config files were copied to project.
   */
  public function testSimpleSamlPhpConfigInit() {
    $this->blt("simplesamlphp:init");
    $this->blt("simplesamlphp:build:config");
    // These commands altered config files, so we mush re-initialize.
    $this->initializeConfig();

    $simpleSamlPhpConfigDirectory = "{$this->sandboxInstance}/simplesamlphp/config";
    $simpleSamlPhpMetadataDirectory = "{$this->sandboxInstance}/simplesamlphp/metadata";

    $this->assertFileExists("$simpleSamlPhpConfigDirectory/authsources.php");
    $this->assertFileExists("$simpleSamlPhpConfigDirectory/config.php");
    $this->assertFileExists("$simpleSamlPhpConfigDirectory/acquia_config.php");

    $configFilePath = "$simpleSamlPhpConfigDirectory/config.php";
    $configFile = file_get_contents($configFilePath);
    $this->assertContains("include 'acquia_config.php';", $configFile);

    $this->assertFileExists("${simpleSamlPhpMetadataDirectory}/saml20-idp-remote.php");
    $this->assertArrayHasKey('simplesamlphp', $this->config->export());
    $this->assertFileExists("{$this->config->get('docroot')}/simplesaml/saml2");
  }

  public function tearDown() {
    $this->bootstrapper->removeSandboxInstance();
    parent::tearDown();
  }

}
