# Feature-based configuration management
## Workflow and best practices for developers and TAs

There are many ways to manage and deploy configuration in Drupal 8, one of which is the Features module. Features allows you to bundle related configuration files (such as a content type and its fields) into individual feature modules. Drupal treats features just like normal modules, but Features and its dependencies add some special sauce that allow features to not only provide default configuration (like normal modules), but to also update (track and import) changes to this configuration.

A note on capitalization and terminology: "Features" is the module on drupal.org, while "features" are the individual collections of configuration on your own project. Also, Features relies heavily on the core configuration management system as well as the contributed Configuration Update module. It's easier to refer to this system collectively as Features, but keep in mind that many bugs and issues may actually relate to the underlying modules.

## Overview of a Features-based workflow
A good Features-based workflow makes it easy for developers to logically bundle configuration into portable version-controlled features that are easy to update. It also makes it easy to deploy these changes and verify that the active configuration on any given site matches what is stored in VCS.

Generally speaking, a configuration change follows this lifecycle:

1. A developer makes the change in her or his local environment.
2. The developer uses the Features UI to export the configuration as a new or updated feature module.
3. The developer commits the new or updated module to VCS and opens a pull request.
4. Automated testing ensures that the feature can be installed from scratch on a new site as well as imported without conflicts on an existing site.
5. After the feature is deployed, deployment hooks automatically import the new or updated configuration.

BLT-based projects already support this workflow, including automatic imports of features during updates (see the `setup:update` BLT task). The task `local:update` can be run by developers to replicate these deployment commands locally.

Be aware that reverting all features and config on every deploy creates a risk of discarding server-side changes. This risk should be controlled by carefully managing permissions, and must be balanced against the greater risk of allowing for divergent configuration between your DB and VCS.

## Best practices

### Using bundles
Features lets you define custom "bundles" that essentially let you train Features to support your project's individual workflow. At the most basic level, they are a way to namespace your features, so you'd want to choose a bundle name based on your project name (an "Acme" bundle would prefix all of your feature machine names with "acme_").

Bundles can also do a lot more to make your life easier. For instance, Features automatically suggests features based around content types and taxonomies. If you'd also like to automatically create features for, say, custom block types, you can configure that preference in your custom bundle. You can also choose to always exclude certain types of configuration (such as permissions--see below), or always group certain types of configuration (such as field storage) into a "core" bundle, which is helpful for breaking circular dependencies.

Note that as of version 8.3.3, Features can manage user roles and permissions, but not in an independent fashion. Permissions can only be exported for an entire role at once, unlike in D7 where you could export roles and their associated permissions separately. For this reason, Features excludes roles and permissions by default. If you wish to export them, change the "alters" setting on your Features bundle. ([reference](https://www.drupal.org/node/2383439))

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

A feature can also stay "overridden" after it is imported, due to another module overriding the provided config. For instance, workbench adds a special field to content types when it is enabled. If this field isn't exported to the feature containing a content type, the feature will be perpetually overridden. This isn't necessarily harmful, but can make it difficult to diagnose other more serious issues. It's recommended to set BLT's CM "allow overrides" property to false to automatically test for overrides.

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

### Updating custom fields and schema
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

### Updating core and contributed modules

Caution must be taken when updating core and contributed modules. If those updates make changes to a module’s configuration or schema, you must make sure to also update your exported features definitions. Otherwise, the next time you run features-import it will import a stale configuration schema and cause unexpected behavior.

The best way to handle this is to always follow these steps when updating contributed and core modules:

1. Start from a clean local setup or refresh. If you are using Features, ensure that there are no overridden features. The `cm.features.no-overrides` flag in [project.yml](https://github.com/acquia/blt/blob/8.x/template/blt/project.yml#L62) can assist with this by halting builds with overridden features.
2. Use `composer update` to download the new module version(s).
3. Run `drush updb` to apply any pending updates locally.
4. If any updates were applied, check if they modified any stored configuration. If using Features, simply check for overridden features. If using core CM, re-export all configuration and check for any changes on disk.
5. Re-export and commit any changes you found in the previous step, along with the updated `composer.json` and `composer.lock`.

We need to find a better way of preventing this than manually monitoring module updates. Find more information in [these](https://www.drupal.org/node/2745685) [issues](https://github.com/acquia/blt/issues/842).

### Overriding configuration

Drupal normally prevents modules from overriding configuration that already exists in the system, producing an exception like this:

    Configuration objects (foo) provided by bar already exist in active configuration

If you need to override the default configuration provided by another project (or core), the available solutions are:

* Recommended: use Features. Features will prevent a PreExistingConfigException from being thrown when a feature containing pre-existing configuration is installed. Ensure that Features is already enabled before installing any individual features that might contain configuration overrides (simply listing Features as a dependency isn't sufficient).
* Move your config into the a custom profile. Configuration imports for Profiles are treated differently than for module. Importing pre-existing configuration for a Profile will not throw a PreExistingConfigException.
* Use [config rewrite](https://www.drupal.org/project/config_rewrite), which will allow you to rewrite the configuration of another module prior to installation.
* Use the [config override system](https://www.drupal.org/docs/8/api/configuration-api/configuration-override-system) built into core. This has [some limitations](https://www.drupal.org/node/2614480#comment-10573274) of which you should be wary.

### Other gotchas

Features is a ground-up rewrite in Drupal 8 and is maturing quickly, but may still have some traps. Developers should keep a close eye on exported features, and architects need to carefully review features in PRs for the gotchas and best practices listed above.
