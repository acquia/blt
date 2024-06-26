<?php

namespace Acquia\Blt\Tests\Robo;

use Acquia\Blt\Robo\Common\TextMunge;
use PHPUnit\Framework\TestCase;

/**
 * Tests text:munge command in blt-console.
 */
class TextMungeTest extends TestCase {

  /**
   * Tests arrayMergeNoDuplicates().
   *
   * @group blt
   *
   * @dataProvider getValueProvider
   */
  public function testArrayMergeNoDuplicates(
    $array1,
    $array2,
    $expected_array,
  ) {
    $munged = TextMunge::arrayMergeNoDuplicates($array1, $array2);
    $this->assertEquals($munged, $expected_array);
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
          '/acquia-pipelines.yaml',
        ],
        [
          '/.vagrant',
          '/acquia-pipelines.yaml',
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
          '/acquia-pipelines.yaml',
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
          '/acquia-pipelines.yaml',
        ],
        [],
        [
          '/.drush-use',
          '/.idea',
          '/.travis.yml',
          '/.vagrant',
          '/acquia-pipelines.yaml',
        ],
      ],
      [
        [],
        [
          '/.drush-use',
          '/.idea',
          '/.travis.yml',
          '/.vagrant',
          '/acquia-pipelines.yaml',
        ],
        [
          '/.drush-use',
          '/.idea',
          '/.travis.yml',
          '/.vagrant',
          '/acquia-pipelines.yaml',
        ],
      ],
    ];
  }

}
