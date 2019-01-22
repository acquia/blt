# Setting up BLT with ddev

### Steps
0. [Install ddev](https://ddev.readthedocs.io/en/stable/#installation)
1. export SITENAME=ddevlovesblt
2. composer create-project --no-interaction acquia/blt-project ddevloveblt
3. ddev config --docroot docroot --projectname $SITENAME --projecttype drupal8
4. ddev start
5. ddev ssh
6. blt 

ddev config yaml needs this update too:

Edit `ddevloveblt/.ddev/config.yaml`

```yaml
  hooks:
  post-start:
  - exec: "ln -sf /var/www/html/vendor/acquia/blt/bin/blt /usr/bin/blt"
  - exec: bash -c "sudo apt-get update && sudo apt-get install -y php7.1-bz2"
  ```

NOTE: Replace ddevloveblt with whatever you want to use for your project name.
