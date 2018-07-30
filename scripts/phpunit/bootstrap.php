<?php

// PHPUnit 4 to PHPUnit 6 bridge. Tests written for PHPUnit 4 need to work on
// PHPUnit 6 with a minimum of fuss.
//
// Code adopted from core/tests/bootstrap.php.
if (version_compare($phpunit_version, '6.1', '>=')) {
  class_alias('\PHPUnit\Framework\AssertionFailedError', '\PHPUnit_Framework_AssertionFailedError');
  class_alias('\PHPUnit\Framework\Constraint\Count', '\PHPUnit_Framework_Constraint_Count');
  class_alias('\PHPUnit\Framework\Error\Error', '\PHPUnit_Framework_Error');
  class_alias('\PHPUnit\Framework\Error\Warning', '\PHPUnit_Framework_Error_Warning');
  class_alias('\PHPUnit\Framework\ExpectationFailedException', '\PHPUnit_Framework_ExpectationFailedException');
  class_alias('\PHPUnit\Framework\Exception', '\PHPUnit_Framework_Exception');
  class_alias('\PHPUnit\Framework\MockObject\Matcher\InvokedRecorder', '\PHPUnit_Framework_MockObject_Matcher_InvokedRecorder');
  class_alias('\PHPUnit\Framework\SkippedTestError', '\PHPUnit_Framework_SkippedTestError');
  class_alias('\PHPUnit\Framework\TestCase', '\PHPUnit_Framework_TestCase');
  class_alias('\PHPUnit\Util\Test', '\PHPUnit_Util_Test');
  class_alias('\PHPUnit\Util\Xml', '\PHPUnit_Util_XML');
}
