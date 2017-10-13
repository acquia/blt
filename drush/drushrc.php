<?php

/**
 * @file
 * Examples of valid statements for a Drush runtime config (drushrc) file.
 *
 * Use this file to cut down on typing out lengthy and repetitive command line
 * options in the Drush commands you use and to avoid mistakes.
 *
 * Rename this file to drushrc.php and optionally copy it to one of the places
 * listed below in order of precedence:
 *
 * 1.  Drupal site folder (e.g. sites/{default|example.com}/drushrc.php).
 * 2.  Drupal /drush and sites/all/drush folders, or the /drush folder
 *       in the directory above the Drupal root.
 * 3.  In any location, as specified by the --config (-c) option.
 * 4.  User's .drush folder (i.e. ~/.drush/drushrc.php).
 * 5.  System wide configuration folder (e.g. /etc/drush/drushrc.php).
 * 6.  Drush installation folder.
 *
 * If a configuration file is found in any of the above locations, it will be
 * loaded and merged with other configuration files in the search list.
 *
 * If you have some configuration options that are specific to a particular
 * version of Drush, then you may place them in a file called drush5rc.php.
 * The version-specific file is loaded in addition to, and after, the general-
 * purpose drushrc file.  Version-specific configuration files can be placed
 * in any of the locations specified above.
 *
 * IMPORTANT NOTE regarding configuration file on Windows:
 *
 * For Windows 7, Windows Vista, Windows Server 2008 and later versions is the
 * system window configuration folder C:\ProgramData\Drush.  For previous
 * versions of Windows is the folder C:\Documents and Settings\All Users\Drush.
 *
 * IMPORTANT NOTE on configuration file loading:
 *
 * At its core, Drush works by "bootstrapping" the Drupal environment in very
 * much the same way that is done during a normal page request from the web
 * server, so most Drush commands run in the context of a fully-initialized
 * website.
 *
 * Configuration files are loaded in the reverse order they are shown above. All
 * configuration files are loaded in the first bootstrapping phase, but
 * a configuration file in a specific Drupal site folder other than the default
 * (eg, sites/example.com/drushrc.php) will not be loaded unless a specific
 * Drupal site is selected.  However, it _will_ be loaded if a site is selected
 * (either via the current working directory or by use of the --uri option),
 * even if the Drush command being run does not bootstrap to the Drupal Site
 * phase.
 *
 * The Drush commands 'rsync' and 'sql-sync' are special cases.  These commands
 * will load the configuration file for the site specified by the source
 * parameter; however, they do not load the configuration file for the site
 * specified by the destination parameter, nor do they load configuration files
 * for remote sites.
 *
 * See `drush topic docs-bootstrap` for more information on how bootstrapping
 * affects the loading of Drush configuration files.
 */

if (getenv('PROBO_ENVIRONMENT') && getenv('BUILD_DOMAIN')) {
  $options['uri'] = $_ENV['BUILD_DOMAIN'];
}

if (getenv('TUGBOAT_URL')) {
  $options['uri'] = $_ENV['TUGBOAT_URL'];
}

// Include current directory. Will add policy.drush.inc.
$options['include'][] = __DIR__;

// If we are on Acquia Cloud, add Acquia specific commands.
if (file_exists('/usr/local/drush8/commands')) {
  $options['include'][] = '/usr/local/drush8/commands';
}
