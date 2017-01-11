<?php

namespace Acquia\Blt\Annotations;

// Applying coding standards here would break the annotations functionality.
// @codingStandardsIgnoreStart
/**
 * @Annotation
 * @Target({"METHOD","PROPERTY"})
 */
class Update
{
  /** @Required */
  public $version;

  /**
   * @var string
   * @Required
   */
  public $description;
}
// @codingStandardsIgnoreEnd
