# Feature-based configuration management
## Workflow and best practices for developers and TAs

There are many ways to manage and deploy configuration in Drupal 8, one of which is the Features module. Features allows you to bundle related configuration files (such as a content type and its fields) into individual feature modules. Drupal treats features just like normal modules, but Features and its dependencies add some special sauce that allow features to not only provide default configuration (like normal modules), but to also update (track and import) changes to this configuration.

A note on capitalization and terminology: "Features" is the module on drupal.org, while "features" are the individual collections of configuration on your own project. This document will try (and probably fail) to use them consistently. Also, Features relies heavily on the core configuration management system as well as the contributed Configuration Update module. It's easier to refer to this system collectively as Features, but in fairness a lot of the the gotchas and brokenness are with the underlying modules.

## Overview of a Features-based workflow
A good Features-based workflow should make it easy for developers to logically bundle configuration into portable version-controlled features that are easy to update. It should also make it easy for a TA to deploy these changes and verify that the active configuration on any given site matches what is stored in VCS.

Generally speaking, a configuration change follows this lifecycle:

1. A developer makes the change in her or his local environment.
2. The developer uses the Features UI to export the configuration as a new or updated feature module.
3. The developer commits the new or updated module to VCS and opens a pull request.
4. Automated testing ensures that the feature can be installed from scratch on a new site as well as imported without conflicts on an existing site.
5. After the feature is deployed, deployment hooks automatically import the new or updated configuration.

As you can see, adding or updating configuration *should* be as easy as exporting the configuration via the Features UI and letting continuous integration processes handle the rest. Unfortunately, due to Features' immaturity in D8, there are a few limitations and best practices to be aware of.

## Gotchas and best practices

### Using bundles
Features lets you define custom "bundles" that essentially let you train Features to support your project's individual workflow. At the most basic level, they are a way to namespace your features, so you'd want to choose a bundle name based on your project name (an "Acme" bundle would prefix all of your feature machine names with "acme_").

Bundles can also do a lot more to make your life easier. For instance, Features automatically suggests features based around content types and taxonomies. If you'd also like to automatically create features for, say, custom block types, you can configure that preference in your custom bundle. You can also choose to always exclude certain types of configuration (such as permissions--see below), or always group certain types of configuration (such as field storage) into a "core" bundle, which is super helpful for breaking circular dependencies (see below).

Unfortunately, for now it seems that bundles are only stored in the database, so your project bundle needs to be created in a system-of-record environment (i.e. production) and then copied down by each developer.

### Managing roles and permissions
Sorry, you [can't do it with Features](https://www.drupal.org/node/2383439). Don't even try, you will be sorely disappointed (as will your database's existing roles and permissions, which will be deleted). For this reason, it's recommended that you use Bundles (see above) to exclude roles from features.

Instead, you'll want to use Drupal's core configuration management system for this by storing role exports in the `config/default` directory and using `drush config-import --partial`to import them on deploys.

### Testing features
You must, must, must test your features. Did I mention you _must_ do this? Seriously, you will regret not doing this.

There are many reasons that features can fail to install or import properly. The most frequent cause is circular dependencies. For instance, imagine that feature A depends on a field exported in feature B, and feature B depends on a field exported in feature B. Neither feature can be enabled first, and site installs will break. This may not be a big deal if you only have a single-site installation, but if you are building a multi-site platform this is something you want to catch early.

Features can also fail on update for a number of reasons (see below). Most frequently, a feature stays "overridden" after it is imported, due to another module overriding the provided config. For instance, workbench adds a special field to content types when it is enabled. If this field isn't exported to the feature containing a content type, the feature will be perpetually overridden.

For these reasons, it's important to have developers test importing their own features, and to have automated tests that install sites from scratch using all available features. You can use the following code snippet in your profile's install file to enable all features in a given bundle:

    <?php
    $available_modules = system_rebuild_module_data();
    $dependencies = array();
    foreach ($available_modules as $name => $module) {
      if ($module->info['package'] == 'My Bundle') {
        $dependencies[] = $name;
      }
      \Drupal::service('module_installer')->install($dependencies);
    }
    
### Features doesn't do that, a.k.a. workarounds
There are some configuration changes that Features doesn’t handle well, or doesn’t handle at all, including:

* Updating field storage (e.g. changing a single-value field to an unlimited-value field)
* Adding a [new custom block type](https://www.drupal.org/node/2702659) to an existing feature (sadly, you have to create a new feature for every block type)
* Deleting a field (you'll want to remove the field from the feature and then use the code snippet below to actually delete the field)
* Adding a field to some types of content (such as [block content](https://www.drupal.org/node/2661806))
* Adding multiple config entities at once that depend on one another (leading to [cryptic exceptions](https://www.drupal.org/node/2726839) when you run features-import... use the workaround below)

To handle these things, you'll want to use update hooks. For instance, you can use the following snippet of code to create or delete a field:

    use Drupal\field\Entity\FieldStorageConfig;
    use Drupal\field\Entity\FieldConfig;

    // Create a new field.
    module_load_include('profile', ‘foo', 'foo'); // See below; foo is your profile name.
    $storage_values = foo_read_config('field.storage.block_content.field_my_new_field', 'foo_feature');
    FieldStorageConfig::create($storage_values)->save();
    $field_values = foo_read_config('field.field.block_content.foo_my_block.field_my_new_field', 'foo_feature');
    FieldConfig::create($field_values)->save();

    // Delete an existing field.
    $field = FieldStorageConfig::loadByName('block_content', 'field_my_field');
    $field->delete();

This depends on a helper function like this, which I suggest adding to your custom profile (Lightning includes this out of the box):

    use Drupal\Core\Config\FileStorage;
    use Drupal\Core\Config\InstallStorage;
    
    /**
     * Reads a stored config file from a module's config/install directory.
     *
     * @param string $id
     *   The config ID.
     * @param string $module
     *   (optional) The module to search. Defaults to 'foo' profile (not technically
     *   a module, but profiles are treated like modules by the install system).
     *
     * @return array
     *   The config data.
     */
    function foo_read_config($id, $module = 'foo') {
      // Statically cache all FileStorage objects, keyed by module.
      static $storage = [];
    
      if (empty($storage[$module])) {
        $dir = \Drupal::service('module_handler')->getModule($module)->getPath();
        $storage[$module] = new FileStorage($dir . '/' . InstallStorage::CONFIG_INSTALL_DIRECTORY);
      }
      return $storage[$module]->read($id);
    }

Additionally, there are some inherent limitations with the Drupal 8 configuration management system:

* You can't "split up" an individual configuration file. For instance, the Acquia Connector module exports all of its settings, including its secret API key, into a single settings file (sigh). There's no way to prevent that key from being exported, so you generally wouldn't want to export this file at all.
* You can't export a config file that is provided by a contributed module. For instance, if the Pathauto module provides default configuration settings, you can't export your own Pathauto settings with Features (or rather you can, but if you try to install the feature from scratch you will get a fatal error).

In both of these cases, the only workaround for now is to manage the configuration via update hooks instead of config files (this is the approach used by Lightning). Fortunately, configuration is easy to manage in code:

    $theme_settings = \Drupal::configFactory()->getEditable('system.theme');
    $theme_settings->set('default', 'my_theme')->save(TRUE);

Unfortunately, you lose the ability to track these changes and your active configuration is at risk of "drift". Hey Drupal community, we'd love to see a solution to this! (a [recent Features patch](https://www.drupal.org/node/2704181) may or may not help this situation).

### Other gotchas

Be aware that since Features is a ground-up rewrite in Drupal 8 and still quite young, there are still a lot of bugs to work out. For instance, when you export a feature the UI will frequently try to automatically include [unrelated configuration](https://www.drupal.org/node/2720167) or dependencies (or worse, it will [forget to include](https://www.drupal.org/node/2666836) very necessary dependencies, such as the field storage for field instances!) Developers just need to keep a close eye on this when exporting features, and TA need to carefully review features in PRs for the gotchas and best practices listed above.

## Getting set up on Acquia Cloud
When setting up a project on Acquia Cloud, it's recommended to add Cloud Hooks for post-code-deploy, post-code-update, and post-db-copy that will automatically perform the following steps:

1. Clear caches: `drush cache-rebuild`
2. Import configuration changes: `drush config-import --partial vcs`
3. Import all features: `drush features-import-all`
4. Run updates: `drush updb --entity-updates`
5. Clear caches again: `drush cache-rebuild`

Note that clearing caches before performing any other steps is recommended because Drush seems to have a rather buggy command cache that will sporadically "forget" that features commands exist. You'll know you've been bit if Drush complains that the Features module is not enabled, even if you've just run `drush en features`.

Reverting all features and config on every deploy may seem dangerous, and it's true that there's a risk of blowing away server-side changes. This should be controlled by carefully locking down permissions. My experience is that it's better to discover these sorts of conflicts early in the development of a platform, rather than to realize several months in that you have wildly divergent or missing configuration between your DB and VCS.

The task `local:update` can be run by developers to replicate these deployment commands locally.
