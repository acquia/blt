This directory is used to aid in the development and testing of BLT.

Use the following commands to create a testable BLT-created project:

```
cp -R blt/blt-project .
cd blt-project
composer install
export $PATH=$(pwd)/vendor/bin:$PATH
blt init
blt configure
composer update
blt setup
blt test
blt deploy:build
```

This will fully create, BLTify, install, tests, and generate a deployment artifact for the blt-project. The acquia/blt dependency in blt-project will be symlinked to ../blt, which enables simple iterative development and testing of both BLT and a BLT-created project in parallel.
