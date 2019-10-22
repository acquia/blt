# Setting up BLT with Docksal

By default, BLT is set to work with Drupal VM. It is easy to set up to use with Docksal, and there are two easy ways
to set this up.

## Setting Your Existing Project to Use Docksal

You can install the [Docksal BLT plugin](../plugins) and run the commands to setup your BLT site to use Docksal.

## Creating a New BLT Project Using Docksal

If you have [Docksal](https://docksal.io) installed and want to create a new BLT project, use `fin project create`
wizard and select Drupal 8 (BLT Version) from the list. This will download the BLT Boilerplate Project and run the 
included `fin init` command. This will download all composer require dependencies, set proper database settings for BLT, 
include the BLT addon command, and install the Drupal site.