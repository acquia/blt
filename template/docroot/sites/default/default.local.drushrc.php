<?php
/**
 * @file
 * Local drush configuration for this project.
 * 
 * This will be copied to local.drushrc.php by BLT when `blt setup`
 * or `blt local:setup` is run. Phing placeholders, like
 * ${project.local.uri}, will be replaced in the target file.
 */

$options['uri'] = '${project.local.uri}';
