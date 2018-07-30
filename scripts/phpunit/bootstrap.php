<?php

// Compatibility for PHPUnit < 6 and PHPUnit 6+.
if (class_exists('PHPUnit_Framework_TestSuite') === true && class_exists('PHPUnit\Framework\TestSuite') === false) {
    class_alias('PHPUnit_Framework_TestSuite', 'PHPUnit\Framework\TestSuite');
}
if (class_exists('PHPUnit_Framework_TestCase') === true && class_exists('PHPUnit\Framework\TestCase') === false) {
    class_alias('PHPUnit_Framework_TestCase', 'PHPUnit\Framework\TestCase');
}
if (class_exists('PHPUnit_TextUI_TestRunner') === true && class_exists('PHPUnit\TextUI\TestRunner') === false) {
    class_alias('PHPUnit_TextUI_TestRunner', 'PHPUnit\TextUI\TestRunner');
}
if (class_exists('PHPUnit_Framework_TestResult') === true && class_exists('PHPUnit\Framework\TestResult') === false) {
    class_alias('PHPUnit_Framework_TestResult', 'PHPUnit\Framework\TestResult');
}
