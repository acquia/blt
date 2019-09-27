<?php

namespace Acquia\Blt\Robo\Common;

use Acquia\Blt\Robo\Exceptions\BltException;

/**
 * Class ComposerWriter.
 *
 * @package Acquia\Blt\Robo\Common
 *
 * Reads and writes to composer.json in a friendly and consistent way.
 */
class ComposerJson {

  /**
   * Filepath.
   *
   * @var string
   */
  private $filepath;

  /**
   * Composer.json contents.
   *
   * @var array
   */
  public $contents;

  /**
   * ComposerWriter constructor.
   *
   * @param string $directory
   *   Directory holding composer.json.
   * @param string $filename
   *   Typically composer.json.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  public function __construct(string $directory, string $filename = 'composer.json') {
    $this->filepath = $directory . DIRECTORY_SEPARATOR . $filename;
    if (!file_exists($this->filepath)) {
      throw new BltException("Could not find composer.json");
    }
    $this->contents = json_decode(file_get_contents($this->filepath), TRUE);
  }

  /**
   * Writes contents to file.
   */
  public function write() {
    // Ensure that require and require-dev are objects and not arrays.
    if (array_key_exists('require', $this->contents) && is_array($this->contents['require'])) {
      ksort($this->contents['require']);
      $this->contents['require'] = (object) $this->contents['require'];
    }
    if (array_key_exists('require-dev', $this->contents)&& is_array($this->contents['require-dev'])) {
      ksort($this->contents['require-dev']);
      $this->contents['require-dev'] = (object) $this->contents['require-dev'];
    }
    file_put_contents($this->filepath, json_encode($this->contents, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
  }

}
