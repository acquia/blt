<?php

namespace Acquia\Blt\Tests\Robo;

use Acquia\Blt\Robo\Common\ArrayManipulator;
use PHPUnit\Framework\TestCase;

/**
 * Tests the ArrayManipulator class.
 */
class ArrayManipulatorTest extends TestCase {

  /**
   * Tests ArrayManipulator::arrayMergeRecursiveExceptEmpty().
   *
   * @dataProvider providerTestArrayMergeRecursiveDistinct
   */
  public function testArrayMergeRecursiveDistinct(
    $array1,
    $array2,
    $expected_array
  ) {
    $this->assertEquals(ArrayManipulator::arrayMergeRecursiveDistinct($array1,
      $array2), $expected_array);
  }

  /**
   * Provides values to testArrayMergeRecursiveDistinct().
   *
   * @return array
   *   An array of values to test.
   */
  public function providerTestArrayMergeRecursiveDistinct() {

    return [
      [
        [
          'modules' => [
            'local' => [
              'enable' => ['test'],
            ],
            'ci' => [
              'uninstall' => ['shield'],
            ],
          ],
          'behat' => [
            'tags' => 'test',
            'launch-selenium' => 'true',
          ],
        ],
        [
          'modules' => [
            'local' => [
              'enable' => [],
            ],
            'ci' => [
              'uninstall' => ['shield'],
            ],
          ],
          'behat' => [
            'tags' => 'nottest',
          ],
        ],
        [
          'modules' => [
            'local' => [
              'enable' => ['test'],
            ],
            'ci' => [
              'uninstall' => ['shield'],
            ],
          ],
          'behat' => [
            'tags' => 'nottest',
            'launch-selenium' => 'true',
          ],
        ],
      ],
    ];
  }

  /**
   * Tests ArrayManipulator::expandFromDotNotatedKeys().
   *
   * @dataProvider providerDotNotatedKeys
   */
  public function testExpandFromDotNotatedKeys($subject, $expected) {
    $this->assertEquals($expected,
      ArrayManipulator::expandFromDotNotatedKeys($subject));
  }

  /**
   * Tests ArrayManipulator::flattenToDotNotatedKeys().
   *
   * @dataProvider providerDotNotatedKeys
   */
  public function testFlattenToDotNotatedKeys($expected, $subject) {
    $this->assertEquals($expected,
      ArrayManipulator::flattenToDotNotatedKeys($subject));
  }

  /**
   * Provider to ExpandFromDotNotatedKeys() & testFlattenToDotNotatedKeys().
   *
   * @return array
   *   An array of test values to test.
   */
  public function providerDotNotatedKeys() {
    return [
      [['first.second' => 'value'], ['first' => ['second' => 'value']]],
    ];
  }

  /**
   * Tests ArrayManipulator::convertArrayToFlatTextArray().
   */
  public function testConvertArrayToFlatTextArray() {
    $array = [
      'first' => [
        'second' => [
          'third' => 'fourth',
        ],
        'fifth' => ['black', 'white'],
        'sixth' => TRUE,
      ],
    ];
    $expected = [
      0 => [
        0 => 'first.second.third',
        1 => 'fourth',
      ],
      1 => [
        0 => 'first.fifth.0',
        1 => 'black',
      ],
      2 => [
        0 => 'first.fifth.1',
        1 => 'white',
      ],
      3 => [
        0 => 'first.sixth',
        1 => 'true',
      ],
    ];
    $this->assertEquals($expected,
      ArrayManipulator::convertArrayToFlatTextArray($array));
  }

}
