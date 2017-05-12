<?php

namespace Acquia\Blt\Annotations;

// Applying coding standards here would break the annotations functionality.
// @codingStandardsIgnoreStart
/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Fileset
{
  /**
   * @var string
   * @Required
   */
  public $id;
}
// @codingStandardsIgnoreEnd
