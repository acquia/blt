<?php

namespace Acquia\Blt\Annotations;

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
