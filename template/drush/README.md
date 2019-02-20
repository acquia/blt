# Drush configuration and aliases

The `drush` directory is intended to contain drush configuration that is not site or environment specific.

Site specific drush configuration lives in `drush/sites/[site-name]`.

## Site aliases

### For remote environments

It's recommended to install Drush aliases in your repository that all developers can use to access your remote sites (i.e. `drush @mysite.dev uli`). 

#### Acquia Cloud Aliases

You can download aliases for Acquia Cloud sites by logging into https://accounts.acquia.com and going to the _Credentials_ tab on your user profile. Download and place the relevant alias file into `drush/sites`.

You can also generate aliases using `blt recipes:aliases:init:acquia` to generate your aliases and place them in the `drush/sites` directory.

*Warning* this is a destructive operation and will wipe all existing aliases in the file named <your subscription>.yml. You should carefully review the output of this recipe prior to committing (to ensure that local aliases or other manual customizations are not lost). 
