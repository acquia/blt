<?php

namespace Acquia\Blt\Tests\Blt;

use Acquia\Blt\Robo\Common\TextMunge;

/**
 * Tests text:munge command in blt-console.
 */
class TextMungeTest extends \PHPUnit_Framework_TestCase {

  /**
   * Tests arrayMergeNoDuplicates().
   *
   * @dataProvider getValueProvider
   */
  public function testArrayMergeNoDuplicates(
    $array1,
    $array2,
    $expected_array
  ) {
    $this->assertEquals(TextMunge::arrayMergeNoDuplicates($array1,
      $array2), $expected_array);
  }

  /**
   * Provides values to testArrayMergeNoDuplicates().
   *
   * @return array
   *   An array of values to test.
   */
  public function getValueProvider() {
    return [
      [
        [
          '/.drush-use',
          '/.idea',
          '/.travis.yml',
          '/.vagrant',
          '/acquia-pipelines.yml',
        ],
        [
          '/.vagrant',
          '/acquia-pipelines.yml',
          '/blt.sh',
          '/box',
          '/build',
          '/additionalfile.txt',
        ],
        [
          '/.drush-use',
          '/.idea',
          '/.travis.yml',
          '/.vagrant',
          '/acquia-pipelines.yml',
          '/blt.sh',
          '/box',
          '/build',
          '/additionalfile.txt',
        ],
      ],
      [
        [
          '/.drush-use',
          '/.idea',
          '/.travis.yml',
          '/.vagrant',
          '/acquia-pipelines.yml',
        ],
        [],
        [
          '/.drush-use',
          '/.idea',
          '/.travis.yml',
          '/.vagrant',
          '/acquia-pipelines.yml',
        ],
      ],
      [
        [],
        [
          '/.drush-use',
          '/.idea',
          '/.travis.yml',
          '/.vagrant',
          '/acquia-pipelines.yml',
        ],
        [
          '/.drush-use',
          '/.idea',
          '/.travis.yml',
          '/.vagrant',
          '/acquia-pipelines.yml',
        ],
      ],
    ];
  }

}
