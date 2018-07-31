<?php

/**
 * @file
 */

// Alias PHPUnit classes for backwards compatibility.
include '../../scripts/phpunit/bootstrap.php';

use Acquia\Blt\Tests\SandboxManager;

$sandbox_manager = new SandboxManager();
$sandbox_manager->bootstrap();
