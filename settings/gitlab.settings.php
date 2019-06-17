<?php

/**
 * @file
 * GitLab environment specific settings.
 */

/**
 * Overwrite CI default database host name.
 *
 * @see ci.settings.php
 */
$databases['default']['default']['host'] = 'mysql';
