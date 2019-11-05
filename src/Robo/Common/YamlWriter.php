<?php

namespace Acquia\Blt\Robo\Common;

use Consolidation\Comments\Comments;
use Symfony\Component\Yaml\Yaml;

/**
 * Class YamlWriter.
 *
 * @package Acquia\Blt\Robo\Common
 *
 * Reads and writes to YAML files in a friendly and consistent way, preserving
 * comments when possible.
 */
class YamlWriter {

  /**
   * Filepath.
   *
   * @var string
   */
  private $filepath;

  /**
   * YAML contents.
   *
   * @var false|string
   */
  private $contents;

  /**
   * YamlWriter constructor.
   *
   * @param string $filepath
   *   Filepath.
   */
  public function __construct($filepath) {
    $this->filepath = $filepath;
    $this->contents = file_get_contents($filepath);
  }

  /**
   * Get contents.
   *
   * @return array
   *   Array.
   */
  public function getContents() {
    return Yaml::parse($this->contents);
  }

  /**
   * Writes contents to file, preserving comments.
   *
   * @param array $yaml
   *   Yaml.
   */
  public function write(array $yaml) {
    $alteredContents = Yaml::dump($yaml, PHP_INT_MAX, 2);
    $commentManager = new Comments();
    $commentManager->collect(explode("\n", $this->contents));
    $alteredWithComments = $commentManager->inject(explode("\n", $alteredContents));
    $result = implode("\n", $alteredWithComments);
    $result .= "\n";
    file_put_contents($this->filepath, $result);
  }

}
