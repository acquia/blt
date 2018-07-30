<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Robo\Common\YamlMunge;
use PHPUnit\Framework\TestCase;

/**
 * Tests yaml:munge command in blt-console.
 */
class YamlMungeTest extends TestCase {

  /**
   * Tests arrayMergeRecursiveExceptEmpty().
   *
   * @group blt
   *
   * @dataProvider getValueProvider
   */
  public function testArrayMergeRecursiveExceptEmpty(
    $array1,
    $array2,
    $expected_array
  ) {
    $this->assertEquals(YamlMunge::arrayMergeRecursiveExceptEmpty($array1,
      $array2), $expected_array);
  }

  /**
   * Provides values to testArrayMergeRecursiveExceptEmpty().
   *
   * @return array
   *   An array of values to test.
   */
  public function getValueProvider() {

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
              'enable' => [],
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

}
