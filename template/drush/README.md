# Drush configuration and aliases

The `drush` directory is intended to contain drush configuration that is not site or environment specific.

Site specific drush configuration lives in `sites/[site-name]`.

## Site aliases

### For remote environments

You should add the Drush aliases for any remote environments (such as Acquia Cloud) to the `sites` directory. This allows developers to access remote environments using simple aliases such as `drush @mysite.dev uli`. Note that if you are using Acquia Cloud, developers can also download these aliases manually from their Insight account, but providing them with the project makes everyone’s life a little easier.

You can find these aliases for Acquia Cloud sites by logging into https://accounts.acquia.com and going to the _Credentials_ tab on your user profile. Download and place the relevant alias file into `sites`.
