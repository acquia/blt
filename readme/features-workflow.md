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

## Best practices

### Using bundles
Features lets you define custom "bundles" that essentially let you train Features to support your project's individual workflow. At the most basic level, they are a way to namespace your features, so you'd want to choose a bundle name based on your project name (an "Acme" bundle would prefix all of your feature machine names with "acme_").

Bundles can also do a lot more to make your life easier. For instance, Features automatically suggests features based around content types and taxonomies. If you'd also like to automatically create features for, say, custom block types, you can configure that preference in your custom bundle. You can also choose to always exclude certain types of configuration (such as permissions--see below), or always group certain types of configuration (such as field storage) into a "core" bundle, which is super helpful for breaking circular dependencies (see below).

### Config vs content
Drupal’s core config system (and by extension, the Features ecosystem) cannot be used to manage entities that Drupal considers to be content, such as nodes, taxonomy terms, and files. This can create conflicts when a configuration entity depends on a content entity, such as:

* You have a block type that includes a file upload field, and you want to place the block in a theme and export the block as a feature.
* You have a view that is filtered by a static taxonomy term, and you want to export that view as a feature.

In these cases, the exported configuration file for the block or view will contain a defined dependency on a content object (referenced by UUID). If that content doesn’t exist when the feature is installed, the installation will fail.

The solution is to make sure that the referenced content exists before the feature is installed. There are currently two recommended methods for this:

* Use the default_content module to export the referenced content as JSON files, and store these files with your feature or in a dependency.
* Use Migrate and a custom module to create default content from any number of custom sources, such as JSON files stored with your feature.

### Testing features
It’s important to ensure via automated testing that features can be installed on a new site as well as enabled on existing sites.

There are many reasons that features can fail to install or import properly. The most frequent cause is circular dependencies. For instance, imagine that feature A depends on a field exported in feature B, and feature B depends on a field exported in feature B. Neither feature can be enabled first, and site installs will break. This may not be a big deal if you only have a single-site installation, but if you are building a multi-site platform this is something you want to catch early.

Features can also fail on update for a number of reasons (see below). Most frequently, a feature stays "overridden" after it is imported, due to another module overriding the provided config. For instance, workbench adds a special field to content types when it is enabled. If this field isn't exported to the feature containing a content type, the feature will be perpetually overridden.

You can use the following code snippet in your profile's install file to enable all features in a given bundle:

    <?php
    $available_modules = system_rebuild_module_data();
    $dependencies = array();
    foreach ($available_modules as $name => $module) {
      if ($module->info['package'] == 'My Bundle') {
        $dependencies[] = $name;
      }
      \Drupal::service('module_installer')->install($dependencies);
    }

## Gotchas and workarounds

### Customizing features
Commonly, you will need to modify the configuration provided by a feature, such as:
* Adding an explicit dependency on another module
* Adding custom install and update hooks
* Adding other custom code to provide custom functionality that’s closely tied to the feature

It’s a bad idea to try to modify the exported feature module to add this functionality, because Features will most likely [overwrite](https://www.drupal.org/node/2720155) or [delete](https://www.drupal.org/node/2710089) your changes the next time you export the feature. Additionally, manually modifying a feature is risky in the first place, because it’s easy to unknowingly break a configuration export.

A safer alternative is to create a separate wrapper module to contain any custom functionality and have this module depend on your feature in order to segregate Feature-managed and manually-managed code.

### Managing roles and permissions
You can manage roles with Features, but not individual permissions ([reference](https://www.drupal.org/node/2383439)). For this reason, it's recommended that you use Bundles (see above) to exclude roles from features.

Instead, you'll want to use Drupal's core configuration management system for this by storing role exports in the `config/default` directory and using `drush config-import --partial`to import them on deploys.

### Updating fields and schema
There are some configuration changes that Features (and the core config system) doesn’t handle well, including:

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

Additionally, an inherent limitation of the Drupal 8 configuration system is that you can't "split up" an individual configuration file. For instance, the Acquia Connector module exports all of its settings, including its secret API key, into a single settings file (sigh). There's no way to prevent that key from being exported, so you generally wouldn't want to export this file at all. You can work around this by managing the configuration via update hooks instead of config files. For instance, Lightning uses this snippet to set the active theme:

    $theme_settings = \Drupal::configFactory()->getEditable('system.theme');
    $theme_settings->set('default', 'my_theme')->save(TRUE);

Finally, you have to be careful when updating core and contributed modules. If those updates make changes to a module’s configuration schema, you must make sure to also update your exported features definitions. Otherwise, the next time you run features-import it will import a stale configuration schema and cause unexpected behavior. We need to find a better way of preventing this than manually monitoring module updates. Find more information in [this discussion](https://www.drupal.org/node/2745685).

### Overriding configuration

If you need to override the default configuration provided by another project (or core), the available solutions are:

* Use a feature module. Features will prevent a PreExistingConfigException from being thrown when a feature containing pre-existing configuration is installed. It is recommended that you add a dependency on the features module in your feature module to ensure that features is actually enabled during installation.
* Move your config into the a custom profile. Configuration imports for Profiles are treated differently than for module. Importing pre-existing configuration for a Profile will not throw a PreExistingConfigException.
* Use [config rewrite](https://www.drupal.org/project/config_rewrite), which will allow you to rewrite the configuration of another module prior to installation.
* Use the [config override system](https://www.drupal.org/docs/8/api/configuration-api/configuration-override-system) built into core. This has [some limitations](https://www.drupal.org/node/2614480#comment-10573274) of which you should be wary.

Using a feature module is the recommended approach.

### Other gotchas

Features is a ground-up rewrite in Drupal 8 and is maturing quickly, but may still have some traps. Developers should keep a close eye on exported features, and TA need to carefully review features in PRs for the gotchas and best practices listed above.

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
