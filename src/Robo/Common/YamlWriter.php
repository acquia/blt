<?php

namespace Acquia\Blt\Robo\Common;

use Consolidation\Comments\Comments;
use Symfony\Component\Yaml\Yaml;

/**
 * Class YamlWriter
 *
 * @package Acquia\Blt\Robo\Common
 *
 * Reads and writes to YAML files in a friendly and consistent way, preserving
 * comments when possible.
 */
class YamlWriter {
  private $filepath;
  private $contents;

  /**
   * YamlWriter constructor.
   *
   * @param $filepath
   */
  public function __construct($filepath) {
    $this->filepath = $filepath;
    $this->contents = file_get_contents($filepath);
  }

  /**
   * @return array
   */
  public function getContents() {
    return Yaml::parse($this->contents);
  }

  /**
   * Writes contents to file, preserving comments.
   *
   * @param array $yaml
   */
  public function write(array $yaml) {
    $alteredContents = Yaml::dump($yaml, PHP_INT_MAX, 2);
    $commentManager = new Comments();
    $commentManager->collect(explode("\n", $this->contents));
    $alteredWithComments = $commentManager->inject(explode("\n", $alteredContents));
    $result = implode("\n", $alteredWithComments);
    file_put_contents($this->filepath, $result);
  }

}
