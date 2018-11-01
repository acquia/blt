# Change Log

#### 9.2.1 (2018-11-01)

[Full Changelog](https://github.com/acquia/blt/compare/9.2.0-alpha3...9.2.1)

**Implemented enhancements**

- Update to Drupal Coder 8.3.x. (#3132)

**Fixed bugs**

- Fixes #2945 by removing cloud hook exception for ACSF. (#2946)

**Miscellaneous**

- Update deploy.md with correct Cloud Hooks links. (#3188)
- Update BLT settings for site and http/proxy detection (#3172)
- Prevent cache collisions in multisite install tasks (#3171)
- Prevent cache collisions in multisite db-update tasks (#3166)
- Updating CHANGELOG.md and setting version for 9.2.0.
- Travis shouldn't duplicate Drupal 8.6 tests (#3152)
- Fixed travis output. (#3151)
- 3149: Minor grammar fix (#3150)
- Fix PHP syntax error in acquia simplesamlphp config. (#3141)
- Update support policies (#3147)
- Resolves #3106: Add 'skip_permissions_hardening' setting to local settings file template (#3107)
- Fixes #3118: Update theme path to more standardized D8 defaults. (#3119)
- Fix INSTALL.md installation directions for Drush Launcher. (#3127)
- updating blt docs to cover ACSF memcache use. (#3136)
- Remove PHP 5.6 from the list of tested versions. (#3137)
- Fix the link of Memcache documentation page. (#3135)
- Minor variable / comment refactor. (#3111)
- Update README.md (#3113)
- Update config_split.config_split.ci.yml (#3129)


#### 9.2.0 (2018-10-09)

[Full Changelog](https://github.com/acquia/blt/compare/9.2.0-alpha3...9.2.0)


**Fixed bugs**

- Fixes #2945 by removing cloud hook exception for ACSF. (#2946)

**Miscellaneous**

- Travis shouldn't duplicate Drupal 8.6 tests (#3152)
- Fixed travis output. (#3151)
- 3149: Minor grammar fix (#3150)
- Fix PHP syntax error in acquia simplesamlphp config. (#3141)
- Update support policies (#3147)
- Resolves #3106: Add 'skip_permissions_hardening' setting to local settings file template (#3107)
- Fixes #3118: Update theme path to more standardized D8 defaults. (#3119)
- Fix INSTALL.md installation directions for Drush Launcher. (#3127)
- updating blt docs to cover ACSF memcache use. (#3136)
- Remove PHP 5.6 from the list of tested versions. (#3137)
- Fix the link of Memcache documentation page. (#3135)
- Minor variable / comment refactor. (#3111)
- Update README.md (#3113)
- Update config_split.config_split.ci.yml (#3129)


#### 9.2.0-alpha3 (2018-09-15)

[Full Changelog](https://github.com/acquia/blt/compare/9.2.0-alpha2...9.2.0-alpha3)

**Implemented enhancements**

- Fixes #3065: Use webflo/drupal-core-require-dev instead of tracking core dev dependencies. (#3068)
- Fixes #3053: Update requirement to drush/drush:^9.4.0. (#3078)

**Fixed bugs**

- Fixes #3046: Cleaned up Pipelines docs. (#3104)
- Cherry-pick in lost/abandoned commits from 9.x (#3072)

**Miscellaneous**

- Exclude the 'sites/settings' dir for the default list of sites. (#2994) (#3105)
- Run validation without interaction on CI. (#3070) (#3097)
- Remove support for PHPUnit 5. (#3102)
- Support memcache on ACSF via flags. (#3096)
- amotic -> atomic (#3098)
- Use core's PHPUnit bootstrap file. (#3092)
- Remove memcache.yml and references to it. (#3093) (#3094)
- Update local-development.md (#3087)
- Run simplesaml config command on all composer calls. (#3051)
- Travis CI Memcache Service and Settings (#3082)


#### 9.2.0-alpha2 (2018-09-08)

[Full Changelog](https://github.com/acquia/blt/compare/9.2.0-alpha1...9.2.0-alpha2)

**Implemented enhancements**

- Allow PHPUnit to bootstrap from core. (#3071)

**Fixed bugs**

- Finished updating memcache config for alpha7. (#3076)
- Fixes #3055: Update memcache settings to match changes in Drupal memcache module. (#3058) (#3067)

**Miscellaneous**

- Clarified Git hook documentation. (#3073)


#### 9.2.0-alpha1 (2018-09-06)

[Full Changelog](https://github.com/acquia/blt/compare/9.1.3...9.2.0-alpha1)


**Miscellaneous**

- Updating versions of Drupal core and Lightning. (#3061)
- Test against Drupal 8.6.x-dev. (#2937)
- Adding the BLT logo to the README. (#3040)
- Added acquia/drupal-spec-tool to Composer suggestions. (#3025)
- Use tab indents for *.mk makefiles (#3018)
- Add PHPUnit bridge adapter. (#3008)
- Added mention of recipes:config:init:splits in config split documentation. (#3020)
- Acquia PHP SDK V2 back to stable release (#3012)
- Fix GitHub typos (#3019)


#### 9.1.2 (2018-08-16)

[Full Changelog](https://github.com/acquia/blt/compare/9.1.1...9.1.2)


**Fixed bugs**

- Fixes #2969: RTD "edit on github" link (#2998)
- Fixes #2988: Correcting notice text to indicate correct config setting. (#2991)
- Correcting the vm command invocation. (#2979)

**Miscellaneous**

- Minor code review docs update (#3013)
- SAML Config Refactor (#2953)
- Improve template/README.md (#3004)
- Multisite setup enhancements and bugfixes (#2997)
- Update Drupal core required version (#2987)


#### 9.1.1 (2018-08-03)

[Full Changelog](https://github.com/acquia/blt/compare/9.1.0-alpha1...9.1.1)

**Implemented enhancements**

- Fixes #2771: Support newer PHPUnit versions (#2982)
- Add support for generating ACSF site aliases (#2961)
- Adding PHP 7.2 to the version testing list. (#2965)
- Fixes #2880: Re-add ACSF tools. (#2911)
- Added Acquia Cloud support to BLT alias and command. (#2896)

**Fixed bugs**

- Fixes #2967: Explicitly invoke drupal:hash-salt:init in blt:init:settings (#2978)
- Fixes #2981: Correct alias to use consistent variable naming. (#2986)
- Addresses #2890: Adding documentation telling people to install Ansible to contribute. (#2977)
- Correct invocation of wizard command. (#2974)
- Fixes #2962: bad documentation for setting up SimpleSAMLphp (#2963)
- Fixes #2906: Set mysql_user array when user supplies new multisite db credentials. (#2908)
- Reduce drush verbosity on CI. (#2636)
- Fixes #2864 to run a redundant config import for config splits. (#2865)
- Adding directions for clearing TravisCI cache as discussed in #2877. (#2903)
- Restructure documentation to conform to new readthedocs standards. (#2894)

**Miscellaneous**

- Fix phpunit version detection (#2990)
- Fixed typo in blt.yml (#2976)
- Add comment to blt.yml explaining where to find a list of available properties (#2975)
- Add tips for using BLT with Lando (#2958)
- 2959 - WSL filemode fix (#2960)
- Updating CHANGELOG.md and setting version for 9.1.0.
- Minor docs update
- Fixed #2867: Cache clear errors on ACSF deploys. (#2922)
- Updates around drush 9 files, removing drush 8 file references. (#2927)
- Update VmCommand.php (#2935)
- Markdown formatting fix to lists render properly
- Fix broken links in Getting Started section (#2928)
- Add Drupal 'trans' tag to Twig linter. (#2831)
- Fixes #2863: Update documentation to include recipe:multisite:init. (#2905)
- Fixed drush cc error on ACSF. (#2862)
- Minor typo correction and wording change.
- Adding FAQ. (#2902)
- Updating BLT release process to include updating blt-project. (#2866)
- Fix link (#2889)
- Fix typo: add missing "have" (#2888)
- Correct readme links pointing to github 8.x branch blobs. (#2887)
- Documentation updates (#2882)
- Fix composer patches creating extraneous core directories. (#2816)
- Minor typo fix


#### 9.1.0 (2018-07-21)

[Full Changelog](https://github.com/acquia/blt/compare/9.1.0-alpha1...9.1.0)

**Implemented enhancements**

- Fixes #2880: Re-add ACSF tools. (#2911)
- Added Acquia Cloud support to BLT alias and command. (#2896)

**Fixed bugs**

- Fixes #2906: Set mysql_user array when user supplies new multisite db credentials. (#2908)
- Reduce drush verbosity on CI. (#2636)
- Fixes #2864 to run a redundant config import for config splits. (#2865)
- Adding directions for clearing TravisCI cache as discussed in #2877. (#2903)
- Restructure documentation to conform to new readthedocs standards. (#2894)

**Miscellaneous**

- Minor docs update
- Fixed #2867: Cache clear errors on ACSF deploys. (#2922)
- Updates around drush 9 files, removing drush 8 file references. (#2927)
- Update VmCommand.php (#2935)
- Markdown formatting fix to lists render properly
- Fix broken links in Getting Started section (#2928)
- Add Drupal 'trans' tag to Twig linter. (#2831)
- Fixes #2863: Update documentation to include recipe:multisite:init. (#2905)
- Fixed drush cc error on ACSF. (#2862)
- Minor typo correction and wording change.
- Adding FAQ. (#2902)
- Updating BLT release process to include updating blt-project. (#2866)
- Fix link (#2889)
- Fix typo: add missing "have" (#2888)
- Correct readme links pointing to github 8.x branch blobs. (#2887)
- Documentation updates (#2882)
- Fix composer patches creating extraneous core directories. (#2816)
- Minor typo fix


#### 9.1.0-alpha1 (2018-06-08)

[Full Changelog](https://github.com/acquia/blt/compare/9.0.5...9.1.0-alpha1)

**Implemented enhancements**

- Reverted changes to solr config script for DrupalVM. (#2852)
- Update drush to ^9.3.0. (#2849)
- PHPStorm documentation (#2843)
- Update README.md (#2819)
- Cleaning up INSTALL.md (#2812)
- Update local-development.md (#2803)
- Remove pipes from deploy sanitize find command (#2798)
- Remove bootstrap line from behat config. (#2783)
- Adding support for factory hooks. (#2787)
- Update DrupalVM for multiple dbs. (#2778)
- Exclude .github folder from artifact. (#2773)
- Require ACSF ^2.47.0. (#2772)
- Feature/deploy gitignore improvements (#2767)
- Fixes #2697: Remove disabled git hooks. (#2702)
- Allow config_splits to be enabled when config files are not exported. (#2716)
- adding the apache vhost for the Drupal VM Dashboard (#2712)
- Fixes #2696 to include config in composer munge. (#2701)
- Add example auth user Behat test. (#2669)
- Suggest typhonius/acquia_cli v1.0+ (#2659)

**Fixed bugs**

- Fixes #2857: Documentation lists non-existant command. (#2858)
- ACSF Factory Hooks, Deploy tasks, and Config bugfixes (#2846)
- Issue #2796: Fixes failing deploys due to config status check. (#2802)
- Fixed Solr in DrupalVM. (#2841)
- Updated consolidation/robo to use ~1.2.4. (#2833)
- Fix issue 2806, wrong files patch in ACSF envs. (#2807)
- Fixes #2789: Require minimum Composer API version. (#2790)
- Removing acsf-tools. (#2786)
- Fixes #2762 to include missing use statement. (#2763)
- Fixes #2720: Write deployment identifier using robo task. (#2726)
- Fixes #2718: Can't git commit image files. (#2752)
- Fixes #2747 to define target_env for BLT 9.x. (#2748)
- Fixes #2742 to include a composer munge as part of the 9.1 update process. (#2745)
- Fixes #2707 by resyncing the ci.blt.yml with the template file. (#2737)
- patch to fix #1038 (#2733)
- Fixes #2658 by moving sanitize command out of hook and into blt command. (#2675)
- Fixes #2705: moving features override functionality and re-enabling. (#2706)
- Prevent unintended config export. (#2680)
- Fixes #2686: Add deployment_identifier to project gitignore. (#2690)
- Remove over-aggressive Icon .gitignore rule. (#2683)
- Fixes #2662: Allow newer version of chrome-mink-driver. (#2674)

**Miscellaneous**

- Update RELEASE.md
- Update RELEASE.md
- Update RELEASE.md
- Update RELEASE.md
- Update Blt.php
- Updating CHANGELOG.md and setting version for 9.1.0-alpha.
- Update RELEASE.md
- Fixes #2830 to default back to PHP 7.1 in the VM. (#2840)
- #2820 Add use statement for ClassLoader to memcache settings. (#2821)
- Cleaning up onboarding doc (#2810)
- Fixes #2727 to direct users to docs.acquia site. (#2800)
- Fixes #2797: Pass option instead of argument when generating deployment identifier. (#2799)
- Update SyncCommand.php
- Updating CHANGELOG.md and setting version for 9.1.0.
- Update onboarding.md
- Update INSTALL.md (#2788)
- Updating BLT README template to include more information about working with BLT + Git. (#2735)
- Fixes #2759: Alias should search for vendor in cwd. (#2777)
- Fixes #2723: Disable post-code-deploy cloud hook for acsf builds. (#2768)
- Fixes #2760: Leverage source and target dump options for sync. (#2770)
- Fixed a little typo. (#2774)
- Fixes #2758: Add notice about composer.suggested.json. (#2764)
- update link to wiki page in inline comment (#2766)
- Fix broken links to CONTRIBUTING.md. (#2754)
- Fixes #2728 to properly document Drush 9 alias generation. (#2751)
- Update dependency-management.md
- Fixes #2713: Lock XDebug into an older version for compatibility with PHP 5.6. (#2714)
- Update README.md
- Fixes #2688: Adding ConfigContext to behat config by default. (#2693)
- Revert back to requiring php 5.6 as the minimum php version (#2665)
- Change `docroot/sites/mysite/site.yml` to `docroot/sites/mysite/blt.yml` (#2678)
- Fixes #2670: Remove deprecated drush docs.
- Update README.md
- Fixes #2582: Config documentation fixes. (#2668)
- Fixes #2644: Further encourage use of vagrant ssh prior to running commands. (#2656)


#### 9.1.0-alpha (2018-06-04)

[Full Changelog](https://github.com/acquia/blt/compare/9.0.5...9.1.0-alpha)

**Implemented enhancements**

- Remove pipes from deploy sanitize find command (#2798)
- Remove bootstrap line from behat config. (#2783)
- Update DrupalVM for multiple dbs. (#2778)
- Exclude .github folder from artifact. (#2773)
- Require ACSF ^2.47.0. (#2772)
- Feature/deploy gitignore improvements (#2767)
- Fixes #2697: Remove disabled git hooks. (#2702)
- Allow config_splits to be enabled when config files are not exported. (#2716)
- adding the apache vhost for the Drupal VM Dashboard (#2712)
- Fixes #2696 to include config in composer munge. (#2701)
- Add example auth user Behat test. (#2669)
- Suggest typhonius/acquia_cli v1.0+ (#2659)

**Fixed bugs**

- Issue #2796: Fixes failing deploys due to config status check. (#2802)
- Fixed Solr in DrupalVM. (#2841)
- Fixes #2789: Require minimum Composer API version. (#2790)
- Removing acsf-tools. (#2786)
- Fixes #2762 to include missing use statement. (#2763)
- Fixes #2720: Write deployment identifier using robo task. (#2726)
- Fixes #2718: Can't git commit image files. (#2752)
- Fixes #2747 to define target_env for BLT 9.x. (#2748)
- Fixes #2742 to include a composer munge as part of the 9.1 update process. (#2745)
- Fixes #2707 by resyncing the ci.blt.yml with the template file. (#2737)
- patch to fix #1038 (#2733)
- Fixes #2658 by moving sanitize command out of hook and into blt command. (#2675)
- Fixes #2705: moving features override functionality and re-enabling. (#2706)
- Prevent unintended config export. (#2680)
- Fixes #2686: Add deployment_identifier to project gitignore. (#2690)
- Remove over-aggressive Icon .gitignore rule. (#2683)
- Fixes #2662: Allow newer version of chrome-mink-driver. (#2674)

**Miscellaneous**

- Update RELEASE.md
- Update drush to ^9.3.0. (#2849)
- PHPStorm documentation (#2843)
- Fixes #2830 to default back to PHP 7.1 in the VM. (#2840)
- Updated consolidation/robo to use ~1.2.4. (#2833)
- #2820 Add use statement for ClassLoader to memcache settings. (#2821)
- Update README.md (#2819)
- Cleaning up INSTALL.md (#2812)
- Fix issue 2806, wrong files patch in ACSF envs. (#2807)
- Cleaning up onboarding doc (#2810)
- Update local-development.md (#2803)
- Fixes #2727 to direct users to docs.acquia site. (#2800)
- Fixes #2797: Pass option instead of argument when generating deployment identifier. (#2799)
- Update SyncCommand.php
- Updating CHANGELOG.md and setting version for 9.1.0.
- Update onboarding.md
- Update INSTALL.md (#2788)
- Updating BLT README template to include more information about working with BLT + Git. (#2735)
- Adding support for factory hooks. (#2787)
- Fixes #2759: Alias should search for vendor in cwd. (#2777)
- Fixes #2723: Disable post-code-deploy cloud hook for acsf builds. (#2768)
- Fixes #2760: Leverage source and target dump options for sync. (#2770)
- Fixed a little typo. (#2774)
- Fixes #2758: Add notice about composer.suggested.json. (#2764)
- update link to wiki page in inline comment (#2766)
- Fix broken links to CONTRIBUTING.md. (#2754)
- Fixes #2728 to properly document Drush 9 alias generation. (#2751)
- Update dependency-management.md
- Fixes #2713: Lock XDebug into an older version for compatibility with PHP 5.6. (#2714)
- Update README.md
- Fixes #2688: Adding ConfigContext to behat config by default. (#2693)
- Revert back to requiring php 5.6 as the minimum php version (#2665)
- Change `docroot/sites/mysite/site.yml` to `docroot/sites/mysite/blt.yml` (#2678)
- Fixes #2670: Remove deprecated drush docs.
- Update README.md
- Fixes #2582: Config documentation fixes. (#2668)
- Fixes #2644: Further encourage use of vagrant ssh prior to running commands. (#2656)


#### 9.1.0 (2018-05-02)

[Full Changelog](https://github.com/acquia/blt/compare/9.0.5...9.1.0)

**Implemented enhancements**

- Remove bootstrap line from behat config. (#2783)
- Update DrupalVM for multiple dbs. (#2778)
- Exclude .github folder from artifact. (#2773)
- Require ACSF ^2.47.0. (#2772)
- Feature/deploy gitignore improvements (#2767)
- Fixes #2697: Remove disabled git hooks. (#2702)
- Allow config_splits to be enabled when config files are not exported. (#2716)
- adding the apache vhost for the Drupal VM Dashboard (#2712)
- Fixes #2696 to include config in composer munge. (#2701)
- Add example auth user Behat test. (#2669)
- Suggest typhonius/acquia_cli v1.0+ (#2659)

**Fixed bugs**

- Fixes #2789: Require minimum Composer API version. (#2790)
- Removing acsf-tools. (#2786)
- Fixes #2762 to include missing use statement. (#2763)
- Fixes #2720: Write deployment identifier using robo task. (#2726)
- Fixes #2718: Can't git commit image files. (#2752)
- Fixes #2747 to define target_env for BLT 9.x. (#2748)
- Fixes #2742 to include a composer munge as part of the 9.1 update process. (#2745)
- Fixes #2707 by resyncing the ci.blt.yml with the template file. (#2737)
- patch to fix #1038 (#2733)
- Fixes #2658 by moving sanitize command out of hook and into blt command. (#2675)
- Fixes #2705: moving features override functionality and re-enabling. (#2706)
- Prevent unintended config export. (#2680)
- Fixes #2686: Add deployment_identifier to project gitignore. (#2690)
- Remove over-aggressive Icon .gitignore rule. (#2683)
- Fixes #2662: Allow newer version of chrome-mink-driver. (#2674)

**Miscellaneous**

- Update onboarding.md
- Update INSTALL.md (#2788)
- Updating BLT README template to include more information about working with BLT + Git. (#2735)
- Adding support for factory hooks. (#2787)
- Fixes #2759: Alias should search for vendor in cwd. (#2777)
- Fixes #2723: Disable post-code-deploy cloud hook for acsf builds. (#2768)
- Fixes #2760: Leverage source and target dump options for sync. (#2770)
- Fixed a little typo. (#2774)
- Fixes #2758: Add notice about composer.suggested.json. (#2764)
- update link to wiki page in inline comment (#2766)
- Fix broken links to CONTRIBUTING.md. (#2754)
- Fixes #2728 to properly document Drush 9 alias generation. (#2751)
- Update dependency-management.md
- Fixes #2713: Lock XDebug into an older version for compatibility with PHP 5.6. (#2714)
- Update README.md
- Fixes #2688: Adding ConfigContext to behat config by default. (#2693)
- Revert back to requiring php 5.6 as the minimum php version (#2665)
- Change `docroot/sites/mysite/site.yml` to `docroot/sites/mysite/blt.yml` (#2678)
- Fixes #2670: Remove deprecated drush docs.
- Update README.md
- Fixes #2582: Config documentation fixes. (#2668)
- Fixes #2644: Further encourage use of vagrant ssh prior to running commands. (#2656)


#### 9.0.5 (2018-03-19)

[Full Changelog](https://github.com/acquia/blt/compare/9.0.4...9.0.5)

**Implemented enhancements**

- checkUriResponse should better indicate whether /etc/hosts is correctly configured (#2655)

**Fixed bugs**

- Fixes #2633: Make git hook symlinks relative. (#2651)

**Miscellaneous**

- Remove custom ArrayInput now that BLT 9 requires Drupal 8.5 and Symfony 3.4. (#2654)
- Fixes #2652: Readme Getting Started links 404. (#2653)
- Fixes #2649: Require DrupalVM 4.8. (#2650)


#### 9.0.4 (2018-03-15)

[Full Changelog](https://github.com/acquia/blt/compare/9.0.3...9.0.4)

**Implemented enhancements**

- Executing all tests in tests/phpunit by default. (#2638)
- Adding messages to 9.0.0 update hook. (#2625)
- Run drush cr instead off cc-drush in SyncCommand. (#2623)

**Fixed bugs**

- Fixes #2639: Add blt/src dir. (#2643)
- Fixes #2633: Make git hook symlinks relative. (#2635)
- #2618: Use proper subcommand name. (#2624)
- Add drupal:sync alias. (#2622)
- Update composer.required.json (#2621)

**Miscellaneous**

- Remove 9.0.4 release notes.
- Fixes #2641: Add workaround for Drush 9 sql:sync bug. (#2642)
- Fixes #2613: Resolve error in BLT wizard. Add tests. (#2626)
- Revert "Fixes #2628: Correctly pass verbosity to Drush." (#2634)
- Update README.md
- Fixes #2628: Correctly pass verbosity to Drush. (#2631)
- Fixes #2629: Incorrect documentation of frontend hooks. (#2630)
- Update adding-to-project.md
- Updating CHANGELOG.md and setting version for 9.0.4.


#### 9.0.3 (2018-03-09)

[Full Changelog](https://github.com/acquia/blt/compare/9.0.2...9.0.3)


**Miscellaneous**

- Fixes #2616: Config Splits not correctly activated. (#2617)


#### 9.0.2 (2018-03-09)

[Full Changelog](https://github.com/acquia/blt/compare/9.0.1...9.0.2)


**Fixed bugs**

- Fix bug causing update failure. (#2615)
- Correctly return failure in cloud hooks. (#2612)

**Miscellaneous**

- Update .travis.yml
- Set VERSION back to 9.x-dev.


#### 9.0.1 (2018-03-08)

[Full Changelog](https://github.com/acquia/blt/compare/9.0.0-rc1...9.0.1)

**Implemented enhancements**

- Alias dev-master to 9.x-dev. (#2609)
- Requiring drupal/core:^8.5.0, acquia/lightning:^3.1.0. (#2608)
- Use --environment option rather than --define. (#2607)

**Fixed bugs**

- Require PHP 7.1 in all the right places. (#2611)

**Miscellaneous**

- Update internal deploy branch setting.
- Set VERSION back to 9.x-dev.
- Updating CHANGELOG.md and setting version for 9.0.0.


#### 9.0.0 (2018-03-08)

[Full Changelog](https://github.com/acquia/blt/compare/9.0.0-rc1...9.0.0)

**Implemented enhancements**

- Use --environment option rather than --define. (#2607)


#### 9.0.0-rc1 (2018-03-06)

[Full Changelog](https://github.com/acquia/blt/compare/9.0.0-beta3...9.0.0-rc1)

**Implemented enhancements**

- Require PHP 7 minimum. (#2601)
- Removing Drupal VM command proxy. (#2597)
- Require drupal/features ^3.7.0. (#2595)
- Use HEAD of main Lightning branch (#2592)
- Update (again) the patch to clear Twig caches on deploys (#2579)
- Add veryVerbose() and debug() methods to drush task. (#2591)
- Refactoring how git hooks are installed so they are found from yml configuration. (#2575)
- Add separate include for GitLab CI. (#2544)
- Moving cloud hook logic to PHP. (#2552)

**Fixed bugs**

- Prevent warnings in update hook. (#2604)
- Execute internal:drupal:install in VM. (#2596)
- Fixes #2580: Recent Project Update throws intimidating error. (#2581)
- Update patch to clear Twig caches on deploys (#2570)
- Fix error message when security-updates test fails (#2568)
- Overwrite phpcs.xml.dist from template. (#2562)
- Fixes #2550: Rogue local.yml file following upgrade. (#2553)
- Fixes #2549: File permission error during upgrade to 9.0.0-beta2. (#2551)

**Miscellaneous**

- Update README.md
- Fixes from pre-release testing. (#2602)
- Drush 9 blt doctor fixes (#2598)
- Fixes #2566: Allow site to be installed from existing config. (#2590)
- Connects to #2582: Disabling BLT commit message. (#2585)
- Update doc references to 9.x. (#2586)
- Fixes #2587: Use strict variable interpolation in git-hooks pre-commit. (#2588)
- Update ISSUE_TEMPLATE.md


#### 9.0.0-beta3 (2018-02-13)

[Full Changelog](https://github.com/acquia/blt/compare/9.0.0-beta2...9.0.0-beta3)

**Implemented enhancements**

- Run long PHPUnit tests separately. (#2545)
- Fixes #2524: Improve output of deprecation validator on failure. (#2536)
- Test Drupal 8.5.x. (#2516)
- Require drush ^9.1.0. (#2530)
- Override superglobals in AC settings includes. (#2528)

**Fixed bugs**

- 2546-fix-simplesamlphp-config-memcache-fatal-error (#2547)
- Fixes #2510: 9.x Updates Fail. (#2535)
- Fixes #2523: Failed Config Import Didn't Cause Failed Build. (#2533)
- BLT-2512: adding execute in VM for config. (#2513)

**Miscellaneous**

- Require drupal/core ^8.5.0-beta1. (#2542)
- Exclude .schema_version from sniffing. (#2534)
- Increasing PHP memory limit.
- Minor comment fix.
- Fixes #2522: BLT Attempted to Reinitializ e existing Git repository. (#2529)
- Update composer.json
- Update ISSUE_TEMPLATE.md


#### 9.0.0-beta2 (2018-01-26)

[Full Changelog](https://github.com/acquia/blt/compare/9.0.0-beta1...9.0.0-beta2)


**Fixed bugs**

- Upgrade path fixes. (#2500)
- Leaving vendor in place during reinstall. (#2497)
- Upgrade path fixes. (#2499)

**Miscellaneous**

- Add correct anchor to release command.
- Fix release command.
- Fix syntax errer.
- Fix version.
- Updating CHANGELOG.md for 9.0.0-beta2.
- Update version to 9.x-dev.


## [8.9.1](https://github.com/acquia/blt/tree/8.9.1) (2017-08-08)
[Full Changelog](https://github.com/acquia/blt/compare/8.9.0...8.9.1)

**Implemented enhancements:**

- Manual setup required for sync:refresh [\#1875](https://github.com/acquia/blt/issues/1875)
- Add ACSF-Tools package require in acsf:init [\#1868](https://github.com/acquia/blt/issues/1868)
- Document that SimpleSAML on PHP 7 should only use database session storage [\#1857](https://github.com/acquia/blt/issues/1857)
- BLT sync:refresh Should Include More Commands [\#1850](https://github.com/acquia/blt/issues/1850)
- Increase Robo Executor Time Out or Make it configurable [\#1840](https://github.com/acquia/blt/issues/1840)
- Allow --exclude-paths to be configured for sync:files [\#1835](https://github.com/acquia/blt/issues/1835)
- Switch to Xenial for Drupal VM base box [\#1830](https://github.com/acquia/blt/issues/1830)
- Allow group options in phpunit [\#1827](https://github.com/acquia/blt/issues/1827)
- Lint the TravisCI YAML file [\#1812](https://github.com/acquia/blt/issues/1812)
- Allow Multiple Configuration Management Strategies [\#1809](https://github.com/acquia/blt/issues/1809)
- Evaluate the inclusion of acquia\_cli tool [\#1800](https://github.com/acquia/blt/issues/1800)
- Allow PHPCS to use DrupalPractice standard [\#1786](https://github.com/acquia/blt/issues/1786)
- Allow deploy\_updates cloud hook function to accept a site parameter [\#1718](https://github.com/acquia/blt/issues/1718)
- Allow PHPCS to Sniff Behat Context Files [\#1523](https://github.com/acquia/blt/issues/1523)
- Re-ignore Behat during PHPCS sniffs. [\#1884](https://github.com/acquia/blt/pull/1884) ([grasmash](https://github.com/grasmash))
- Allowing verbose output from PHPUnit. [\#1883](https://github.com/acquia/blt/pull/1883) ([grasmash](https://github.com/grasmash))
- Fixes \#1787: Better support using BLT for testing core and contrib with PHPUnit. [\#1882](https://github.com/acquia/blt/pull/1882) ([grasmash](https://github.com/grasmash))
- Fixes \#1800: Suggest acquia\_cli tool. [\#1881](https://github.com/acquia/blt/pull/1881) ([grasmash](https://github.com/grasmash))
- Fixes \#1875: Manual setup required for sync:refresh. [\#1877](https://github.com/acquia/blt/pull/1877) ([grasmash](https://github.com/grasmash))
- Defaults Simplesaml to database storage. [\#1876](https://github.com/acquia/blt/pull/1876) ([typhonius](https://github.com/typhonius))
- BLT-1523: allowing behat php files to be sniffed by phpcs. [\#1873](https://github.com/acquia/blt/pull/1873) ([mikemadison13](https://github.com/mikemadison13))
- Acsf init dev [\#1869](https://github.com/acquia/blt/pull/1869) ([msherron](https://github.com/msherron))
- Adding shell to doctor output. [\#1865](https://github.com/acquia/blt/pull/1865) ([grasmash](https://github.com/grasmash))
- Fixes \#1840: Increase Robo Executor Time Out or Make it configurable. [\#1861](https://github.com/acquia/blt/pull/1861) ([grasmash](https://github.com/grasmash))
- Adds default memcache prefix for simplesaml. [\#1856](https://github.com/acquia/blt/pull/1856) ([typhonius](https://github.com/typhonius))
- BLT-1850: adding setup:composer:install and frontend. [\#1851](https://github.com/acquia/blt/pull/1851) ([mikemadison13](https://github.com/mikemadison13))
- Fixes \#1835: provide --exclude-paths configuration key. [\#1846](https://github.com/acquia/blt/pull/1846) ([bobbygryzynger](https://github.com/bobbygryzynger))
- Fix coding standards in simplesamlphp acquia config [\#1832](https://github.com/acquia/blt/pull/1832) ([christopher-hopper](https://github.com/christopher-hopper))
- Fixes \#1830: Switch Drupal VM to Xenial \(16.04 LTS\) to match Acquia Cloud. [\#1831](https://github.com/acquia/blt/pull/1831) ([geerlingguy](https://github.com/geerlingguy))
- Prevent doctrine/inflector from advancing to 1.2.x. [\#1829](https://github.com/acquia/blt/pull/1829) ([timcosgrove](https://github.com/timcosgrove))
- Add additional option support for phpunit. [\#1828](https://github.com/acquia/blt/pull/1828) ([steveworley](https://github.com/steveworley))
- Replacing PHP filesets with php.xml.dist. [\#1820](https://github.com/acquia/blt/pull/1820) ([grasmash](https://github.com/grasmash))
- Fixes \#1786: Allow PHPCS to use DrupalPractice standard. [\#1819](https://github.com/acquia/blt/pull/1819) ([grasmash](https://github.com/grasmash))
- Fixes \#1004: Configurable test steps for fresh installs and live dbs. [\#1818](https://github.com/acquia/blt/pull/1818) ([grasmash](https://github.com/grasmash))
- Allow Drupal-VM config flexibility. [\#1817](https://github.com/acquia/blt/pull/1817) ([dpagini](https://github.com/dpagini))
- Allowing $split to be overridden. [\#1804](https://github.com/acquia/blt/pull/1804) ([grasmash](https://github.com/grasmash))
- Return to containerized Travis builds [\#1802](https://github.com/acquia/blt/pull/1802) ([danepowell](https://github.com/danepowell))
- Moving ExampleTest.php to examples:init command. [\#1783](https://github.com/acquia/blt/pull/1783) ([dpagini](https://github.com/dpagini))
- Provide initial template for Gitlab Pipelines [\#1733](https://github.com/acquia/blt/pull/1733) ([snufkin](https://github.com/snufkin))

**Fixed bugs:**

- Config split ignored during config-import on Acquia cloud [\#1891](https://github.com/acquia/blt/issues/1891)
- sync:files fails due to non-interactive shell [\#1874](https://github.com/acquia/blt/issues/1874)
- Parameters not passed when invokeCommand is used with @executeInDrupalVM [\#1866](https://github.com/acquia/blt/issues/1866)
- validate:phpcs:files fails to find additional violations after an initial violation is found [\#1855](https://github.com/acquia/blt/issues/1855)
- BLT's deploy\_updates bash function doesn't define the environment [\#1854](https://github.com/acquia/blt/issues/1854)
- BLT Sync does not permit drush prompt [\#1852](https://github.com/acquia/blt/issues/1852)
- Files are synced to a subdirectory of files. [\#1845](https://github.com/acquia/blt/issues/1845)
- Make sync:db non interactive on Travis. [\#1841](https://github.com/acquia/blt/issues/1841)
- Incorrect script path causes Probo builds to fail. [\#1824](https://github.com/acquia/blt/issues/1824)
- PHPUnit Fatal Error Doesn't Fail Build [\#1822](https://github.com/acquia/blt/issues/1822)
- blt sync:refresh still prompts for confirmation when `-y` is supplied. [\#1810](https://github.com/acquia/blt/issues/1810)
- Deploy command fails silently [\#1807](https://github.com/acquia/blt/issues/1807)
- Behat tests fail out of the box with Pipelines [\#1799](https://github.com/acquia/blt/issues/1799)
- checkDrupalVm:remote-host always fails [\#1797](https://github.com/acquia/blt/issues/1797)
- sync:refresh doesn't support multisites [\#1580](https://github.com/acquia/blt/issues/1580)
- Sniffing all PHPCS files after commit. [\#1894](https://github.com/acquia/blt/pull/1894) ([grasmash](https://github.com/grasmash))
- Fixes \#1891: Config split ignored during config-import on Acquia cloud. [\#1893](https://github.com/acquia/blt/pull/1893) ([grasmash](https://github.com/grasmash))
- Fixes \#1874: sync:files fails due to non-interactive shell. [\#1880](https://github.com/acquia/blt/pull/1880) ([grasmash](https://github.com/grasmash))
- Fixes \#1870: PHPCS Hanging. [\#1871](https://github.com/acquia/blt/pull/1871) ([grasmash](https://github.com/grasmash))
- BLT-1852 assuming -y when prompting while executing something in blt [\#1862](https://github.com/acquia/blt/pull/1862) ([alex-moreno](https://github.com/alex-moreno))
- Fixes \#1854: BLT's deploy\_updates bash function doesn't define the environment. [\#1860](https://github.com/acquia/blt/pull/1860) ([grasmash](https://github.com/grasmash))
- Fixes \#1841: blt sync issue exit code 75. [\#1859](https://github.com/acquia/blt/pull/1859) ([grasmash](https://github.com/grasmash))
- Fixes \#1855: validate:phpcs:files fails to find additional violations after an initial violation is found. [\#1858](https://github.com/acquia/blt/pull/1858) ([grasmash](https://github.com/grasmash))
- Fixes \#1845: provide trailing slash for files path. [\#1847](https://github.com/acquia/blt/pull/1847) ([bobbygryzynger](https://github.com/bobbygryzynger))
- Fixes \#1843: use invokeCommand on simplesamlphp:deploy:config. [\#1844](https://github.com/acquia/blt/pull/1844) ([bobbygryzynger](https://github.com/bobbygryzynger))
- Preventing Probo builds from failing due to incorrect path. [\#1825](https://github.com/acquia/blt/pull/1825) ([jkaeser](https://github.com/jkaeser))
- Fixes \#1822: PHPUnit Fatal Error Doesn't Fail Build. [\#1823](https://github.com/acquia/blt/pull/1823) ([grasmash](https://github.com/grasmash))
- Fixes \#1810: blt sync:refresh still prompts for confirmation when `-y` is supplied. [\#1815](https://github.com/acquia/blt/pull/1815) ([grasmash](https://github.com/grasmash))
- Fixes \#1797: checkDrupalVm:remote-host always fails. [\#1814](https://github.com/acquia/blt/pull/1814) ([grasmash](https://github.com/grasmash))

**Closed issues:**

- SAML configuration copying fails during the deploy task [\#1843](https://github.com/acquia/blt/issues/1843)
- Simplesamlphp Acquia config file needs improvment [\#1837](https://github.com/acquia/blt/issues/1837)

**Misc merged pull requests**

- Issue \#1875: Execute all sync commands outside VM. [\#1892](https://github.com/acquia/blt/pull/1892) ([danepowell](https://github.com/danepowell))
- Fixes \#849: Documentation: frontend build process. [\#1879](https://github.com/acquia/blt/pull/1879) ([grasmash](https://github.com/grasmash))
- Minor docs update [\#1867](https://github.com/acquia/blt/pull/1867) ([danepowell](https://github.com/danepowell))
- xcodebuild requires sudo [\#1842](https://github.com/acquia/blt/pull/1842) ([alex-moreno](https://github.com/alex-moreno))
- Documenting Probo.CI setup steps. [\#1826](https://github.com/acquia/blt/pull/1826) ([jkaeser](https://github.com/jkaeser))
- Normalize and covert svg line endings to native on checkout [\#1801](https://github.com/acquia/blt/pull/1801) ([zweishar](https://github.com/zweishar))
- Allow non-200 connections when waiting for server for tests. [\#1798](https://github.com/acquia/blt/pull/1798) ([danepowell](https://github.com/danepowell))
- Upgrading wikimedia/composer-merge-plugin to 1.4.1. [\#1795](https://github.com/acquia/blt/pull/1795) ([grasmash](https://github.com/grasmash))
- Allow extra args to be passed into launchChrome. [\#1794](https://github.com/acquia/blt/pull/1794) ([thom8](https://github.com/thom8))

## [8.9.0](https://github.com/acquia/blt/tree/8.9.0) (2017-07-12)
[Full Changelog](https://github.com/acquia/blt/compare/8.9.0-rc3...8.9.0)

**Fixed bugs:**

- config-split cm strategy always uses sync as config directory key [\#1775](https://github.com/acquia/blt/issues/1775)

**Misc merged pull requests**

- Remove duplicate simplesamlphp documentation. [\#1791](https://github.com/acquia/blt/pull/1791) ([greylabel](https://github.com/greylabel))
- Updating documentation. [\#1789](https://github.com/acquia/blt/pull/1789) ([grasmash](https://github.com/grasmash))


## [8.9.0-rc3](https://github.com/acquia/blt/tree/8.9.0-rc3) (2017-07-11)
[Full Changelog](https://github.com/acquia/blt/compare/8.9.0-rc2...8.9.0-rc3)

**Implemented enhancements:**

- Throw exception if minimum PHP version is unmet. [\#1785](https://github.com/acquia/blt/pull/1785) ([grasmash](https://github.com/grasmash))
- Updating settings command. [\#1782](https://github.com/acquia/blt/pull/1782) ([dpagini](https://github.com/dpagini))
- Adding version constant replacement to release command. [\#1774](https://github.com/acquia/blt/pull/1774) ([grasmash](https://github.com/grasmash))

**Fixed bugs:**

- config-split cm strategy always uses sync as config directory key [\#1775](https://github.com/acquia/blt/issues/1775)
- blt vm writes to bashrc in DrupalVM and leaves it unwritable [\#1771](https://github.com/acquia/blt/issues/1771)
- composer create-project fails on Twig patch application [\#1770](https://github.com/acquia/blt/issues/1770)
- Updating behat copy target. [\#1784](https://github.com/acquia/blt/pull/1784) ([dpagini](https://github.com/dpagini))
- Fixes \#1772: Increasing Composer plugin timeout to 1hr. [\#1776](https://github.com/acquia/blt/pull/1776) ([grasmash](https://github.com/grasmash))
- Fixes \#1771: blt vm writes to bashrc in DrupalVM and leaves it unwritable. [\#1773](https://github.com/acquia/blt/pull/1773) ([grasmash](https://github.com/grasmash))

**Misc merged pull requests**

- Update example tests path to match repo structure. [\#1780](https://github.com/acquia/blt/pull/1780) ([greylabel](https://github.com/greylabel))
- Fix typo in Probo CI command description. [\#1779](https://github.com/acquia/blt/pull/1779) ([greylabel](https://github.com/greylabel))


## [8.9.0-rc1](https://github.com/acquia/blt/tree/8.9.0-rc1) (2017-06-29)
[Full Changelog](https://github.com/acquia/blt/compare/8.9.0-beta6...8.9.0-rc1)

**Implemented enhancements:**

- Adding consolidation/config. [\#1739](https://github.com/acquia/blt/pull/1739) ([grasmash](https://github.com/grasmash))
- Checking that class exists before loading Fileset. [\#1737](https://github.com/acquia/blt/pull/1737) ([grasmash](https://github.com/grasmash))

**Fixed bugs:**

- Fixing bug in setting of deploy tag. [\#1741](https://github.com/acquia/blt/pull/1741) ([grasmash](https://github.com/grasmash))
- Fixes \#1722: Travis chrome behat setup. [\#1736](https://github.com/acquia/blt/pull/1736) ([grasmash](https://github.com/grasmash))

**Misc merged pull requests**

- Adding frontend task to setup:build. [\#1740](https://github.com/acquia/blt/pull/1740) ([grasmash](https://github.com/grasmash))
- Fix update message. [\#1738](https://github.com/acquia/blt/pull/1738) ([arknoll](https://github.com/arknoll))
- Revert "Defaulting web driver to Selenium." [\#1735](https://github.com/acquia/blt/pull/1735) ([grasmash](https://github.com/grasmash))


## [8.9.0-beta6](https://github.com/acquia/blt/tree/8.9.0-beta6) (2017-06-27)
[Full Changelog](https://github.com/acquia/blt/compare/8.9.0-beta5...8.9.0-beta6)

**Implemented enhancements:**

- Unneeded .gitignore line about drupal console. [\#1727](https://github.com/acquia/blt/issues/1727)
- Robo feature request: total "setup" time [\#1719](https://github.com/acquia/blt/issues/1719)
- Copying behat.yml and example.local.yml as part of setup:behat. [\#1732](https://github.com/acquia/blt/pull/1732) ([grasmash](https://github.com/grasmash))
- Printing metadata for hook invokations. [\#1731](https://github.com/acquia/blt/pull/1731) ([grasmash](https://github.com/grasmash))
- Fixes \#1719: Robo feature request: total "setup" time. [\#1730](https://github.com/acquia/blt/pull/1730) ([grasmash](https://github.com/grasmash))
- Defaulting web driver to Selenium. [\#1729](https://github.com/acquia/blt/pull/1729) ([grasmash](https://github.com/grasmash))
- \#1727 Removes drupal console related gitignore line. [\#1728](https://github.com/acquia/blt/pull/1728) ([marvil07](https://github.com/marvil07))
- Adds probo.ci settings. [\#1726](https://github.com/acquia/blt/pull/1726) ([typhonius](https://github.com/typhonius))
- Normalize variable usage in travis.yml. [\#1720](https://github.com/acquia/blt/pull/1720) ([greylabel](https://github.com/greylabel))
- Preventing duplicate warnings from being displayed. [\#1716](https://github.com/acquia/blt/pull/1716) ([grasmash](https://github.com/grasmash))

**Fixed bugs:**

- Connects to \#1528: BLT's merged dependencies not installed during deploy [\#1634](https://github.com/acquia/blt/issues/1634)
- Allowing specific Behat features to be tested. [\#1723](https://github.com/acquia/blt/pull/1723) ([grasmash](https://github.com/grasmash))
- Implementing exit\_early after composer install. [\#1721](https://github.com/acquia/blt/pull/1721) ([dpagini](https://github.com/dpagini))

**Closed issues:**

- Unable to run tests via the simpletest UI [\#1724](https://github.com/acquia/blt/issues/1724)

**Misc merged pull requests**

- Issue \#1724: Fixed missing dev dependency. [\#1725](https://github.com/acquia/blt/pull/1725) ([danepowell](https://github.com/danepowell))
- Changing drupalextension version to work with Lightning tests. [\#1717](https://github.com/acquia/blt/pull/1717) ([grasmash](https://github.com/grasmash))
- Ensure travis commands run as 'CI' environment. [\#1715](https://github.com/acquia/blt/pull/1715) ([dpagini](https://github.com/dpagini))


## [8.9.0-beta5](https://github.com/acquia/blt/tree/8.9.0-beta5) (2017-06-21)
[Full Changelog](https://github.com/acquia/blt/compare/8.9.0-beta4...8.9.0-beta5)

**Implemented enhancements:**

- Connects to \#1711: Adding config:get and config:dump commmands. [\#1714](https://github.com/acquia/blt/pull/1714) ([grasmash](https://github.com/grasmash))
- Fixes \#1709: Adding notifications regarding manual upgrade path. [\#1713](https://github.com/acquia/blt/pull/1713) ([grasmash](https://github.com/grasmash))

**Fixed bugs:**

- BLT Upgrade to 8.9.0-beta4 Issues [\#1707](https://github.com/acquia/blt/issues/1707)
- New plugin.php is not executed after BLT update [\#1683](https://github.com/acquia/blt/issues/1683)
- Fixes \#1683: Check schema version before command execution. [\#1710](https://github.com/acquia/blt/pull/1710) ([grasmash](https://github.com/grasmash))

**Misc merged pull requests**

- Fix docs for creating a deploy-exclude.txt file. [\#1712](https://github.com/acquia/blt/pull/1712) ([greylabel](https://github.com/greylabel))
- Fixes \#1707: BLT Upgrade to 8.9.0-beta4 issues. [\#1708](https://github.com/acquia/blt/pull/1708) ([grasmash](https://github.com/grasmash))


## [8.9.0-beta4](https://github.com/acquia/blt/tree/8.9.0-beta4) (2017-06-20)
[Full Changelog](https://github.com/acquia/blt/compare/8.9.0-beta3...8.9.0-beta4)

**Implemented enhancements:**

- Remove ExampleCommand and ExampleHook from template, require `blt examples:init` or something. [\#1669](https://github.com/acquia/blt/issues/1669)
- Fixes \#1669: Remove ExampleCommand and ExampleHook from template, require `blt examples:init` or something. [\#1697](https://github.com/acquia/blt/pull/1697) ([grasmash](https://github.com/grasmash))
- Fixes \#1675: Setting cm.core.dirs.vcs to cm.core.dirs.sync. [\#1695](https://github.com/acquia/blt/pull/1695) ([grasmash](https://github.com/grasmash))
- Issue \#1681: Set vagrant exec default directory in BLT Vagrantfile. [\#1688](https://github.com/acquia/blt/pull/1688) ([geerlingguy](https://github.com/geerlingguy))

**Fixed bugs:**

- blt tests failure [\#1698](https://github.com/acquia/blt/issues/1698)
- Behat Tests Fail [\#1681](https://github.com/acquia/blt/issues/1681)
- Features Import Fails [\#1679](https://github.com/acquia/blt/issues/1679)
- Git Hooks Failing on Commit [\#1677](https://github.com/acquia/blt/issues/1677)
- setup commands not using multisite param [\#1676](https://github.com/acquia/blt/issues/1676)
- BLT Fails to run config-import sync on cloudhooks [\#1675](https://github.com/acquia/blt/issues/1675)
- Config Import Incorrect On Multi-site install [\#1674](https://github.com/acquia/blt/issues/1674)
- Fixes \#1698: blt tests failure. [\#1700](https://github.com/acquia/blt/pull/1700) ([grasmash](https://github.com/grasmash))
- Fixing early return. [\#1696](https://github.com/acquia/blt/pull/1696) ([grasmash](https://github.com/grasmash))
- Connects to \#1681: Fixing Vagrantfile syntax. [\#1694](https://github.com/acquia/blt/pull/1694) ([grasmash](https://github.com/grasmash))
- Fixes \#1675: Use cm.core.key everywhere. [\#1693](https://github.com/acquia/blt/pull/1693) ([grasmash](https://github.com/grasmash))
- Throwing BltException whenever invokeCommand\(\) fails. [\#1692](https://github.com/acquia/blt/pull/1692) ([grasmash](https://github.com/grasmash))
- Fixes \#1674: Config Import Incorrect On Multi-site install. [\#1691](https://github.com/acquia/blt/pull/1691) ([grasmash](https://github.com/grasmash))
- Fixes \#1681: Expand Vagrantfile properties. [\#1690](https://github.com/acquia/blt/pull/1690) ([grasmash](https://github.com/grasmash))
- BLT-1679: correcting features import in config import process. [\#1680](https://github.com/acquia/blt/pull/1680) ([mikemadison13](https://github.com/mikemadison13))

**Closed issues:**

- Instructions to generate a custom profile don't work [\#1682](https://github.com/acquia/blt/issues/1682)
- Unable to set permissions for site directories.  [\#1678](https://github.com/acquia/blt/issues/1678)
- Documentation gap for alternative local development environments [\#1636](https://github.com/acquia/blt/issues/1636)

**Misc merged pull requests**

- Fixes \#1636: Documentation gap for alternative local development environments. [\#1699](https://github.com/acquia/blt/pull/1699) ([grasmash](https://github.com/grasmash))
- Fix a dead link in the tests directory to point to the updated URL on… [\#1689](https://github.com/acquia/blt/pull/1689) ([gabe-connolly](https://github.com/gabe-connolly))
- Fixes \#1682: Instructions to generate a custom profile don't work. [\#1686](https://github.com/acquia/blt/pull/1686) ([grasmash](https://github.com/grasmash))
- Adding @todos. [\#1684](https://github.com/acquia/blt/pull/1684) ([grasmash](https://github.com/grasmash))


## [8.9.0-beta3](https://github.com/acquia/blt/tree/8.9.0-beta3) (2017-06-15)
[Full Changelog](https://github.com/acquia/blt/compare/8.9.0-beta2...8.9.0-beta3)

**Implemented enhancements:**

- Ignoring \*.min.js in validation commands. [\#1672](https://github.com/acquia/blt/pull/1672) ([grasmash](https://github.com/grasmash))
- Adding -y param to `blt:create-project` call. [\#1670](https://github.com/acquia/blt/pull/1670) ([grasmash](https://github.com/grasmash))
- Run the appropriate behat tags when testing on pipelines. [\#1665](https://github.com/acquia/blt/pull/1665) ([arknoll](https://github.com/arknoll))

**Fixed bugs:**

- Setting tests.run-server to false by default. [\#1671](https://github.com/acquia/blt/pull/1671) ([grasmash](https://github.com/grasmash))

**Misc merged pull requests**

- Fix drush site install command to disable update status module. [\#1663](https://github.com/acquia/blt/pull/1663) ([arknoll](https://github.com/arknoll))
- Adding -y param to `blt update` call. [\#1662](https://github.com/acquia/blt/pull/1662) ([grasmash](https://github.com/grasmash))


## [8.9.0-beta2](https://github.com/acquia/blt/tree/8.9.0-beta2) (2017-06-13)
[Full Changelog](https://github.com/acquia/blt/compare/8.9.0-beta1...8.9.0-beta2)

**Implemented enhancements:**

- Don't run ACSF Cloud Hooks in update environments. (#1642) ([danepowell](https://github.com/danepowell))
- Removing .htaccess files from non-docroots. (#1650) ([dpagini](https://github.com/dpagini))
- Importing config splits after site install. [\#1661](https://github.com/acquia/blt/pull/1661) ([grasmash](https://github.com/grasmash))
- Fixing repo.root detection, removing duplicative methods. [\#1660](https://github.com/acquia/blt/pull/1660) ([grasmash](https://github.com/grasmash))
- Don't clobber drush.uri parameter [\#1656](https://github.com/acquia/blt/pull/1656) ([danepowell](https://github.com/danepowell))
- Adding --release-branch arg to release command. [\#1644](https://github.com/acquia/blt/pull/1644) ([grasmash](https://github.com/grasmash))

**Fixed bugs:**

- PHP shebang echoed during commit message validation [\#1657](https://github.com/acquia/blt/issues/1657)
- Front-end Tests not executed during pipelines build [\#1631](https://github.com/acquia/blt/issues/1631)
- Fixed ACSF deploys, don't clobber drush URI [\#1659](https://github.com/acquia/blt/pull/1659) ([danepowell](https://github.com/danepowell))
- Fixed ACSF deploy error [\#1653](https://github.com/acquia/blt/pull/1653) ([danepowell](https://github.com/danepowell))
- Fixed Git fetch/merge during deploy [\#1651](https://github.com/acquia/blt/pull/1651) ([danepowell](https://github.com/danepowell))
- Fixes \#1631: Front-end Tests not executed during pipelines build. [\#1638](https://github.com/acquia/blt/pull/1638) ([grasmash](https://github.com/grasmash))

**Closed issues:**

- Documented Directory structure not created? [\#1637](https://github.com/acquia/blt/issues/1637)

**Misc merged pull requests**

- Fixes \#1657: PHP shebang echoed during commit message validation. [\#1658](https://github.com/acquia/blt/pull/1658) ([grasmash](https://github.com/grasmash))
- Update repo architecture docs [\#1649](https://github.com/acquia/blt/pull/1649) ([danepowell](https://github.com/danepowell))
- Update gitignore patter for libraries to match contrib expectations. [\#1648](https://github.com/acquia/blt/pull/1648) ([greylabel](https://github.com/greylabel))
- Fixing call @launchWebServer annotation in frontend:test. [\#1645](https://github.com/acquia/blt/pull/1645) ([grasmash](https://github.com/grasmash))

## [8.9.0-beta1](https://github.com/acquia/blt/tree/8.9.0-beta1) (2017-06-12)
[Full Changelog](https://github.com/acquia/blt/compare/8.9.0-alpha1...8.9.0-beta1)

**Implemented enhancements:**

- Execute the front-end tests target while the Drush Webserver is active [\#1564](https://github.com/acquia/blt/issues/1564)
- Removed redundant file path setting. [\#1641](https://github.com/acquia/blt/pull/1641) ([danepowell](https://github.com/danepowell))
- Printing error output when runserver fails. [\#1639](https://github.com/acquia/blt/pull/1639) ([grasmash](https://github.com/grasmash))
- Set site\_dir to fix config imports on ACSF. [\#1635](https://github.com/acquia/blt/pull/1635) ([danepowell](https://github.com/danepowell))
- Removed BUILD\_DIR and replaced with SOURCE\_DIR  [\#1633](https://github.com/acquia/blt/pull/1633) ([aweingarten](https://github.com/aweingarten))
- Ensuring that all commands throw exceptions and return status. [\#1625](https://github.com/acquia/blt/pull/1625) ([grasmash](https://github.com/grasmash))
- Fixes \#1564: Added frontend-web-test hook to BLT. [\#1615](https://github.com/acquia/blt/pull/1615) ([grasmash](https://github.com/grasmash))
- Randomize DrupalVM IP address [\#1605](https://github.com/acquia/blt/issues/1605)
- BLT Split settings missing for ODE  [\#1554](https://github.com/acquia/blt/issues/1554)
- Split up "run-tests" [\#1541](https://github.com/acquia/blt/issues/1541)
- Add git version requirements [\#1532](https://github.com/acquia/blt/issues/1532)
- Allow Both Features and Default CMI [\#1481](https://github.com/acquia/blt/issues/1481)
- Execute Tests inside the VM where possible [\#1472](https://github.com/acquia/blt/issues/1472)
- Stop creating a tests/behat/features/Examples.feature file [\#1441](https://github.com/acquia/blt/issues/1441)
- Multisite property suggestions and default/site.yml [\#1423](https://github.com/acquia/blt/issues/1423)
- Deploy to multiple remotes, but not merge from? [\#1415](https://github.com/acquia/blt/issues/1415)
- Speed up builds by conditionally "returning early"? [\#1159](https://github.com/acquia/blt/issues/1159)
- Access to production databases [\#1109](https://github.com/acquia/blt/issues/1109)
- Allow PHPUnit Tests to Bootstrap Drupal [\#1048](https://github.com/acquia/blt/issues/1048)
- New Feature: Allow for Custom PHPUnit Paths [\#1047](https://github.com/acquia/blt/issues/1047)
- Allow custom Twig lint paths [\#1016](https://github.com/acquia/blt/issues/1016)
- Invoke blt frontend from inside drupalVM [\#1009](https://github.com/acquia/blt/issues/1009)
- Execute installation of Lighting on Acquia Cloud environment after Pipelines build [\#975](https://github.com/acquia/blt/issues/975)
- Fixes \#1423: Multisite property suggestions and default/site.yml. [\#1607](https://github.com/acquia/blt/pull/1607) ([grasmash](https://github.com/grasmash))
- Fixes \#1605: Randomize DrupalVM IP address. [\#1606](https://github.com/acquia/blt/pull/1606) ([grasmash](https://github.com/grasmash))
- Refactoring DrushTask-\>run\(\) after upstream changes. [\#1604](https://github.com/acquia/blt/pull/1604) ([grasmash](https://github.com/grasmash))
- Improving detection of DVM state. [\#1601](https://github.com/acquia/blt/pull/1601) ([grasmash](https://github.com/grasmash))
- Fixes \#1582: Improve detection of DVM. [\#1587](https://github.com/acquia/blt/pull/1587) ([grasmash](https://github.com/grasmash))
- Fixes \#1582: @executeInDrupalVm annotation not respected. [\#1585](https://github.com/acquia/blt/pull/1585) ([grasmash](https://github.com/grasmash))
- Run frontend:setup prior to frontend:build. [\#1584](https://github.com/acquia/blt/pull/1584) ([bobbygryzynger](https://github.com/bobbygryzynger))
- Allowing Inspector state to be cleared. [\#1559](https://github.com/acquia/blt/pull/1559) ([grasmash](https://github.com/grasmash))
- Fixes \#\#1554: Added config split settings for Acquia ODEs. [\#1555](https://github.com/acquia/blt/pull/1555) ([aweingarten](https://github.com/aweingarten))
- Fixes \#1532: Add git version requirements. [\#1550](https://github.com/acquia/blt/pull/1550) ([grasmash](https://github.com/grasmash))
- Porting validate:\* and filesets concept. [\#1503](https://github.com/acquia/blt/pull/1503) ([grasmash](https://github.com/grasmash))
- Porting blt:\* commands to Robo. [\#1498](https://github.com/acquia/blt/pull/1498) ([grasmash](https://github.com/grasmash))
- Config module isn't necessary to handle config updates [\#1494](https://github.com/acquia/blt/pull/1494) ([danepowell](https://github.com/danepowell))
- BLT-1490: removing invalid parameter \(partial\). [\#1491](https://github.com/acquia/blt/pull/1491) ([mikemadison13](https://github.com/mikemadison13))
- Fixes \#1481: allowing for combo of features and default config. [\#1488](https://github.com/acquia/blt/pull/1488) ([grasmash](https://github.com/grasmash))
- Refactoring tests:security-updates command. [\#1487](https://github.com/acquia/blt/pull/1487) ([grasmash](https://github.com/grasmash))
- Port simplesamlphp targets to Robo. [\#1485](https://github.com/acquia/blt/pull/1485) ([malikkotob](https://github.com/malikkotob))
- Fixes \#1472: Execute Tests inside the VM where possible. [\#1475](https://github.com/acquia/blt/pull/1475) ([grasmash](https://github.com/grasmash))
- Fixes \#1159: Speed up builds by conditionally "returning early". [\#1468](https://github.com/acquia/blt/pull/1468) ([grasmash](https://github.com/grasmash))
- Making random username generation more efficient. [\#1467](https://github.com/acquia/blt/pull/1467) ([grasmash](https://github.com/grasmash))
- Fixes \#1441: Make Example.feature more generic. [\#1466](https://github.com/acquia/blt/pull/1466) ([grasmash](https://github.com/grasmash))
- Override CommandArguments::option to default option separator. [\#1461](https://github.com/acquia/blt/pull/1461) ([malikkotob](https://github.com/malikkotob))
- Refactor drush task [\#1460](https://github.com/acquia/blt/pull/1460) ([malikkotob](https://github.com/malikkotob))
- Refactoring ConfigCommand to use taskDrush\(\). [\#1455](https://github.com/acquia/blt/pull/1455) ([grasmash](https://github.com/grasmash))
- BLT-1047: adding ability to customize paths for phpunit tests. [\#1435](https://github.com/acquia/blt/pull/1435) ([mikemadison13](https://github.com/mikemadison13))
- Update drupal/simplesamlphp\_auth module. [\#1422](https://github.com/acquia/blt/pull/1422) ([dpagini](https://github.com/dpagini))
- Ensure configuration export integrity [\#1421](https://github.com/acquia/blt/pull/1421) ([danepowell](https://github.com/danepowell))

**Fixed bugs:**

- PHP Memory Limit Errors During Travis Builds [\#1629](https://github.com/acquia/blt/issues/1629)
- Custom Filesets.php overridden by update [\#1621](https://github.com/acquia/blt/issues/1621)
- blt doctor failing \(or drush in vagrant issue\) [\#1617](https://github.com/acquia/blt/issues/1617)
- Fixed \#1621: Custom Filesets.php overridden by update. [\#1624](https://github.com/acquia/blt/pull/1624) ([grasmash](https://github.com/grasmash))
- Fixes \#1617: blt doctor failing \(or drush in vagrant issue\) . [\#1620](https://github.com/acquia/blt/pull/1620) ([grasmash](https://github.com/grasmash))
- Fixing call to non-existant drupal:update command. [\#1614](https://github.com/acquia/blt/pull/1614) ([grasmash](https://github.com/grasmash))
- An alternative default.local.settings.php [\#1610](https://github.com/acquia/blt/issues/1610)
- Running any blt command gives php notices [\#1598](https://github.com/acquia/blt/issues/1598)
- Deployment doesn't import configuration [\#1597](https://github.com/acquia/blt/issues/1597)
- Unexpected NULL results from getOutputData [\#1593](https://github.com/acquia/blt/issues/1593)
- blt doctor only returns error output [\#1590](https://github.com/acquia/blt/issues/1590)
- Connects to \#1582: @executeInDrupalVm not respected during invokeCommand [\#1589](https://github.com/acquia/blt/issues/1589)
- @executeInDrupalVm annotation not respected [\#1582](https://github.com/acquia/blt/issues/1582)
- sync:refresh doesn't support multisites [\#1580](https://github.com/acquia/blt/issues/1580)
- default/settings.php and default/settings/default.local.settings.php wiped out by update [\#1577](https://github.com/acquia/blt/issues/1577)
- local.drushrc.php not created for multisites [\#1576](https://github.com/acquia/blt/issues/1576)
- 8.9.x Head Pipelines Error [\#1538](https://github.com/acquia/blt/issues/1538)
- 8.9.x Head has issues with ZSH [\#1537](https://github.com/acquia/blt/issues/1537)
- Pipelines Build Fail to Complete on 8.9.x [\#1535](https://github.com/acquia/blt/issues/1535)
- Drush not installed when running blt deploy [\#1528](https://github.com/acquia/blt/issues/1528)
- unexpected EOF Error [\#1527](https://github.com/acquia/blt/issues/1527)
- Regression of MySQL Error [\#1526](https://github.com/acquia/blt/issues/1526)
- Pipelines builds hang [\#1511](https://github.com/acquia/blt/issues/1511)
- Scripted updates fail between 8.7.0-beta1 and 8.7.3 [\#1510](https://github.com/acquia/blt/issues/1510)
- Travis CI: MySQL is not available [\#1509](https://github.com/acquia/blt/issues/1509)
- Fatal Error in 8.x.dev \(8.9.x\): PHP Memory Usage  [\#1508](https://github.com/acquia/blt/issues/1508)
- blt local:setup stalls unexpectedly on HEAD [\#1502](https://github.com/acquia/blt/issues/1502)
- Drupal Not Installed Issue w/ Robo & blt tests command [\#1478](https://github.com/acquia/blt/issues/1478)
- blt local:refresh errors [\#1452](https://github.com/acquia/blt/issues/1452)
- Cannot install site in VM [\#1448](https://github.com/acquia/blt/issues/1448)
- Vm::checkRequirements never called during VM setup [\#1446](https://github.com/acquia/blt/issues/1446)
- blt setup:update:features-override-check is dependent on console window size [\#1443](https://github.com/acquia/blt/issues/1443)
- Cannot disable security-updates [\#1440](https://github.com/acquia/blt/issues/1440)
- Spaces in project paths break BLT [\#1328](https://github.com/acquia/blt/issues/1328)
- Fixes \#1610: An alternative default.local.settings.php. [\#1612](https://github.com/acquia/blt/pull/1612) ([grasmash](https://github.com/grasmash))
- Fixes \#1597: Deployment doesn't import configuration. [\#1609](https://github.com/acquia/blt/pull/1609) ([grasmash](https://github.com/grasmash))
- Fixes \#1593: Replacing getOutputData\(\) with getMessage\(\). [\#1603](https://github.com/acquia/blt/pull/1603) ([grasmash](https://github.com/grasmash))
- Fixes \#1580: sync:refresh doesn't support multisites. [\#1599](https://github.com/acquia/blt/pull/1599) ([grasmash](https://github.com/grasmash))
- Fixes \#1576: local.drushrc.php not created for multisites. [\#1596](https://github.com/acquia/blt/pull/1596) ([grasmash](https://github.com/grasmash))
- Fixes \#1589: @executeInDrupalVm not respected during invokeCommand. [\#1595](https://github.com/acquia/blt/pull/1595) ([grasmash](https://github.com/grasmash))
- Fixes \#1590: blt doctor only returns error output. [\#1594](https://github.com/acquia/blt/pull/1594) ([grasmash](https://github.com/grasmash))
- Fixes \#1572: Failing Behat Test Hangs Pipelines Build. [\#1575](https://github.com/acquia/blt/pull/1575) ([grasmash](https://github.com/grasmash))
- Fixing update hook. [\#1569](https://github.com/acquia/blt/pull/1569) ([grasmash](https://github.com/grasmash))
- Fixing deploy:update command. [\#1565](https://github.com/acquia/blt/pull/1565) ([grasmash](https://github.com/grasmash))
- Fixing incorrect call to drupal:install. [\#1557](https://github.com/acquia/blt/pull/1557) ([grasmash](https://github.com/grasmash))
- Disabling command cache if dir is not writable. [\#1553](https://github.com/acquia/blt/pull/1553) ([grasmash](https://github.com/grasmash))
- Fixing SimpleSamlPhpCommand. [\#1552](https://github.com/acquia/blt/pull/1552) ([grasmash](https://github.com/grasmash))
- Fixing typo with create-db option in local:sync:db command. [\#1521](https://github.com/acquia/blt/pull/1521) ([malikkotob](https://github.com/malikkotob))
- Fixes \#1508: Increasing DVM memory\_limit. [\#1520](https://github.com/acquia/blt/pull/1520) ([grasmash](https://github.com/grasmash))
- Killing processes more effectively, in Pipelines. [\#1518](https://github.com/acquia/blt/pull/1518) ([grasmash](https://github.com/grasmash))
- Fixing project creation bugs in 8.9.x. [\#1517](https://github.com/acquia/blt/pull/1517) ([grasmash](https://github.com/grasmash))
- Fixes \#1509, \#1502: Correcting drush.alias settings and preventing ci interaction. [\#1512](https://github.com/acquia/blt/pull/1512) ([grasmash](https://github.com/grasmash))
- Fixing execution of commands in VM. [\#1495](https://github.com/acquia/blt/pull/1495) ([grasmash](https://github.com/grasmash))
- Fixes \#1443: blt setup:update:features-override-check is dependent on console window size. [\#1454](https://github.com/acquia/blt/pull/1454) ([grasmash](https://github.com/grasmash))
- Fixes \#1452: blt local:refresh errors. [\#1453](https://github.com/acquia/blt/pull/1453) ([grasmash](https://github.com/grasmash))
- Fixes \#1446: Vm::checkRequirements never called during VM setup. [\#1450](https://github.com/acquia/blt/pull/1450) ([grasmash](https://github.com/grasmash))
- Fixes \#1448: Cannot install site in VM. [\#1449](https://github.com/acquia/blt/pull/1449) ([grasmash](https://github.com/grasmash))
- Fixed errors for BLT doctor. [\#1447](https://github.com/acquia/blt/pull/1447) ([danepowell](https://github.com/danepowell))
- Fixes \#1328: Spaces in project paths break BLT. [\#1426](https://github.com/acquia/blt/pull/1426) ([grasmash](https://github.com/grasmash))

**Misc merged pull requests**

- Increase DrupalVM memory [\#1630](https://github.com/acquia/blt/pull/1630) ([danepowell](https://github.com/danepowell))
- Increasing Travis CI memory limit to 512Mb. [\#1626](https://github.com/acquia/blt/pull/1626) ([grasmash](https://github.com/grasmash))
- Fix vagrant status output parsing. [\#1619](https://github.com/acquia/blt/pull/1619) ([bobbygryzynger](https://github.com/bobbygryzynger))
- Refactoring all commands to throw BltException on failure. [\#1579](https://github.com/acquia/blt/pull/1579) ([grasmash](https://github.com/grasmash))
- Fixes \#1572: Failing Behat Test Hangs Pipelines Build. [\#1578](https://github.com/acquia/blt/pull/1578) ([grasmash](https://github.com/grasmash))
- Fix formatting. [\#1574](https://github.com/acquia/blt/pull/1574) ([wouteradem](https://github.com/wouteradem))
- Adding setup:toggle-modules command. [\#1571](https://github.com/acquia/blt/pull/1571) ([grasmash](https://github.com/grasmash))
- Creating internal PHPCS and PHPCBF tasks. [\#1549](https://github.com/acquia/blt/pull/1549) ([grasmash](https://github.com/grasmash))
- Expanding config JIT. [\#1547](https://github.com/acquia/blt/pull/1547) ([grasmash](https://github.com/grasmash))
- Improving VM status detection. [\#1546](https://github.com/acquia/blt/pull/1546) ([grasmash](https://github.com/grasmash))
- Updating Robo to 1.0.7. [\#1545](https://github.com/acquia/blt/pull/1545) ([grasmash](https://github.com/grasmash))
- Cleaning up Pipelines output. [\#1544](https://github.com/acquia/blt/pull/1544) ([grasmash](https://github.com/grasmash))
- Adding SSL info for SimpleSAMLphp configuration. [\#1542](https://github.com/acquia/blt/pull/1542) ([wouteradem](https://github.com/wouteradem))
- Attemping to fix ZSH alias issues. [\#1540](https://github.com/acquia/blt/pull/1540) ([grasmash](https://github.com/grasmash))
- Porting install-alias.sh to PHP. [\#1534](https://github.com/acquia/blt/pull/1534) ([grasmash](https://github.com/grasmash))
- Miscellaneous release prep. [\#1533](https://github.com/acquia/blt/pull/1533) ([grasmash](https://github.com/grasmash))
- Remove double quote from drush command. [\#1529](https://github.com/acquia/blt/pull/1529) ([malikkotob](https://github.com/malikkotob))
- Adding a command cache. [\#1519](https://github.com/acquia/blt/pull/1519) ([grasmash](https://github.com/grasmash))
- Ripping out Phing. [\#1507](https://github.com/acquia/blt/pull/1507) ([grasmash](https://github.com/grasmash))
- Porting fix:\* and local:\* namespaces to Robo. [\#1506](https://github.com/acquia/blt/pull/1506) ([grasmash](https://github.com/grasmash))
- Fix Documentation Title typo in mkdocs.yml [\#1497](https://github.com/acquia/blt/pull/1497) ([dasginganinja](https://github.com/dasginganinja))
- Enhance nuke command. [\#1493](https://github.com/acquia/blt/pull/1493) ([dpagini](https://github.com/dpagini))
- BLT-1479: correcting example path to the phpunit.xml.dist file. [\#1480](https://github.com/acquia/blt/pull/1480) ([mikemadison13](https://github.com/mikemadison13))
- Fixes \#1440: Cannot disable security-updates. [\#1473](https://github.com/acquia/blt/pull/1473) ([grasmash](https://github.com/grasmash))
- Halt task execution when options set before drush command. [\#1465](https://github.com/acquia/blt/pull/1465) ([malikkotob](https://github.com/malikkotob))
- Fixing AC hooks. [\#1463](https://github.com/acquia/blt/pull/1463) ([grasmash](https://github.com/grasmash))
- Fixes \#1009: Invoke blt frontend from inside drupalVM. [\#1462](https://github.com/acquia/blt/pull/1462) ([grasmash](https://github.com/grasmash))
- Fixes \#1415: Port deploy:\* commands to Robo. [\#1457](https://github.com/acquia/blt/pull/1457) ([grasmash](https://github.com/grasmash))
- Site install task doesn't respect alias. [\#1451](https://github.com/acquia/blt/pull/1451) ([danepowell](https://github.com/danepowell))

**Closed issues:**

- If the blt command is included in git commit message, blt command will be executed. [\#1592](https://github.com/acquia/blt/issues/1592)
- Failing Behat Test Hangs Pipelines Build [\#1572](https://github.com/acquia/blt/issues/1572)
- Memcache patch breaking blt build [\#1562](https://github.com/acquia/blt/issues/1562)
- How to auto checkout build branches with git post-receive? [\#1501](https://github.com/acquia/blt/issues/1501)
- Typo in documentation title -- "Acquia BLT Documentaton" [\#1496](https://github.com/acquia/blt/issues/1496)
- Invalid Parameter in Configuration Import [\#1490](https://github.com/acquia/blt/issues/1490)
- Incorrect PHPUnit Path for Config File [\#1479](https://github.com/acquia/blt/issues/1479)
- blt local:setup is asking for a password, and I have no idea what it is [\#1477](https://github.com/acquia/blt/issues/1477)

## [8.8.5](https://github.com/acquia/blt/tree/8.8.5) (2017-06-12)
[Full Changelog](https://github.com/acquia/blt/compare/8.8.4...8.8.5)

**Implemented enhancements:**

- Increasing Travis CI memory limit to 512Mb. (#1627)

**Fixed bugs**

- Remove nonexistent ansi option. (#1640)

## [8.7.5](https://github.com/acquia/blt/tree/8.7.5) (2017-06-12)
[Full Changelog](https://github.com/acquia/blt/compare/8.7.4...8.7.5)

**Implemented enhancements:**

- Increasing Travis CI memory limit to 512Mb. (#1628)

## [8.7.4](https://github.com/acquia/blt/tree/8.7.4) (2017-06-06)
[Full Changelog](https://github.com/acquia/blt/compare/8.7.3...8.7.4)

**Implemented enhancements:**

- Move post-config-import target call. (#1522)
- Backporting ArrayManipulator. (#1551)
- Added config split settings for Acquia ODEs. (#1560)

## [8.8.4](https://github.com/acquia/blt/tree/8.8.4) (2017-06-06)
[Full Changelog](https://github.com/acquia/blt/compare/8.8.3..8.8.4)

**Implemented enhancements:**

- Fixes #1597: Deployment doesn't import configuration. (#1608)
- Fixing calls to getOutputData() after upstream Robo change. (#1613)
- Connects to #1566: Adding color to Behat output in Pipelines. (#1583)
- Fixes #1504: Documentation for "Automated testing using live content"… (#1586)
- Killing web server after Behat runs. (#1536)
- Killing processes on Pipelines more effectively.
- Fixes #1440: Cannot disable security-updates. (#1489)

## [8.9.0-alpha1](https://github.com/acquia/blt/tree/8.9.0-alpha1) (2017-04-26)
[Full Changelog](https://github.com/acquia/blt/compare/8.8.3...8.9.0-alpha1)

**Notable changes**:

- The following targets have been moved to Robo:
  - setup:*
  - ascf:*
  - frontend:*
  - drupal:*

**Implemented enhancements:**

- Frontend Target [\#1436](https://github.com/acquia/blt/issues/1436)
- Randomize user 1 name on install [\#1403](https://github.com/acquia/blt/issues/1403)
- Refactoring release command OUT of BLT app. [\#1434](https://github.com/acquia/blt/pull/1434) ([grasmash](https://github.com/grasmash))
- Fixes \#1325: Updating composer-merge-plugin to dev-master. [\#1430](https://github.com/acquia/blt/pull/1430) ([grasmash](https://github.com/grasmash))
- Fixes \#1403: Randomize user 1 name on install. [\#1418](https://github.com/acquia/blt/pull/1418) ([grasmash](https://github.com/grasmash))
- Fixes \#1408: Porting doctor command to Robo. [\#1417](https://github.com/acquia/blt/pull/1417) ([grasmash](https://github.com/grasmash))
- Minor refactor of config settings logic. [\#1416](https://github.com/acquia/blt/pull/1416) ([danepowell](https://github.com/danepowell))
- Create new custom Robo Drush task. [\#1413](https://github.com/acquia/blt/pull/1413) ([malikkotob](https://github.com/malikkotob))
- Allowing interactive\(\) to be detected. [\#1411](https://github.com/acquia/blt/pull/1411) ([grasmash](https://github.com/grasmash))
- Porting setup:\* and drupal:\* commands to Robo. [\#1407](https://github.com/acquia/blt/pull/1407) ([grasmash](https://github.com/grasmash))

**Fixed bugs:**

- Not all BLT commands showing up when running blt list [\#1425](https://github.com/acquia/blt/issues/1425)
- BLT Doctor checkUri reports incorrect uri [\#1408](https://github.com/acquia/blt/issues/1408)
- "vendor/acquia/blt/composer.suggested.json" causing issues with composer [\#1325](https://github.com/acquia/blt/issues/1325)
- Resolve merge bug in wikimedia/composer-merge-plugin [\#1241](https://github.com/acquia/blt/issues/1241)
- BLT Doctor checkNvmExists\(\) on Drupal-VM [\#764](https://github.com/acquia/blt/issues/764)
- Fixes \#1438: Project prefix contains quote characters. [\#1442](https://github.com/acquia/blt/pull/1442) ([grasmash](https://github.com/grasmash))
- Fixes \#1431 - PHP Notice: Undefined index: tests:behat [\#1432](https://github.com/acquia/blt/pull/1432) ([aguasingas](https://github.com/aguasingas))
- Fixes \#1425: Not all BLT commands showing up when running blt list. [\#1427](https://github.com/acquia/blt/pull/1427) ([grasmash](https://github.com/grasmash))
- Prefixing BLT calls with composer.bin. [\#1419](https://github.com/acquia/blt/pull/1419) ([grasmash](https://github.com/grasmash))

**Closed issues:**

- project.prefix including single quotes when validating commit message [\#1438](https://github.com/acquia/blt/issues/1438)
- PHP Notice:  Undefined index: tests:behat [\#1431](https://github.com/acquia/blt/issues/1431)
- MySQL error when trying to run Behat tests on my VM [\#1424](https://github.com/acquia/blt/issues/1424)

**Misc merged pull requests**

- BLT-1436: porting frontend tasks to robo. [\#1437](https://github.com/acquia/blt/pull/1437) ([mikemadison13](https://github.com/mikemadison13))
- Redundant error message is redundant [\#1428](https://github.com/acquia/blt/pull/1428) ([TravisCarden](https://github.com/TravisCarden))
- Update README.md [\#1412](https://github.com/acquia/blt/pull/1412) ([danepowell](https://github.com/danepowell))

## [8.7.3](https://github.com/acquia/blt/tree/8.7.3) (2017-04-21)
[Full changelog](https://github.com/acquia/blt/compare/8.7.2...8.7.3)

- Pass --yes parameter for cloud db sanitize script. (#1410)
- Remove sudo mysql starts (#1406)
- Run config-import twice (#1398)
- BLT 8.7.0 update wipes out pre-existing composer-merge-plugin config.
- Update composer command invocation. (#1387)
- Recommend installing hostsupdater plugin. (#1389)
- Exclude sites/all from multisite variable. (#1388)
- Fixes #1374: BLT upgrade deletes docroot/sites/default/settings/includes.settings.php. (#1385)
- Fixes #1354: BLT 8.7.0 upgrade wipes out custom installer-paths configuration. (#1383)

## [8.8.3](https://github.com/acquia/blt/tree/8.8.3) (2017-04-21)
[Full changelog](https://github.com/acquia/blt/compare/8.8.2...8.8.3)

- Pass --yes parameter for cloud db sanitize script. (#1410)
- Remove sudo mysql starts (#1406)
- Adding explanation of "box" directory. (#1404)
- Issue #53: Fixed git config command. (#1402)
- Minor spelling fix (#1401)
- Fix example command doc comment. (#1399)
- Run config-import twice (#1398)
- Fixes #1390: Fixing strict errors in ExampleHook. (#1396)
- Fixes #1390: Using custom Robo commands within a BLT project. (#1395)
- Using custom Robo commands within a BLT project. (#1393)
- Fixes #1367: Allow extra arguments to be added to `composer install`. (#1394)
- BLT 8.7.0 update wipes out pre-existing composer-merge-plugin config. (#1392)
- Fixes #1377: Failing behat tests still allow travisci to "pass". (#1381)
- Update composer command invocation. (#1387)
- Recommend installing hostsupdater plugin. (#1389)
- Exclude sites/all from multisite variable. (#1388)
- Fixes #1343: Add a command to print out available Behat definitions. (#1384)
- Fixes #1374: BLT upgrade deletes docroot/sites/default/settings/includes.settings.php. (#1385)
- Fixes #1354: BLT 8.7.0 upgrade wipes out custom installer-paths configuration. (#1383)
- Allow BLT tests to pass when blt is not symlinked into place.
- Update project.yml (#1376)

## [8.7.2](https://github.com/acquia/blt/tree/8.7.2) (2017-04-14)
[Full changelog](https://github.com/acquia/blt/compare/8.7.1...8.7.2)

- Disable update module at install time. #1360
- Fix broken core-only config strategy #1358
- Update configuration-management.md #1352
- Update notifications not disabled during Drupal 8.3.x installation #1353
- Travis email error starting with Drupal 8.3.0 and Lightning 2.1.0 #1336
- Removing search_api from suggested modules. #1334
- Examples.feature changes overwritten whenever BLT is updated #1322
- Fixes #1324: Add ah_other config split. #1331
- Fixes #1257 Complete branching and tagging documentation #1321
- Minor cleanup of configuration-management.md #1319

## [8.8.2](https://github.com/acquia/blt/tree/8.8.2) (2017-04-14)
[Full changelog](https://github.com/acquia/blt/compare/8.8.1...8.8.2)

- Update configuration-management.md (#1378)
- Removing memcache patch. (#1373)
- Fixing PHPCS syntax issue.
- Fixing incorrectly referenced DVM alias.
- Fixing Pipelines interactive() bug in tests:behat.

## [8.8.1](https://github.com/acquia/blt/tree/8.8.1) (2017-04-11)
[Full Changelog](https://github.com/acquia/blt/compare/8.8.0...8.8.1)

**Implemented enhancements:**

- Killing web driver more surgically. [\#1365](https://github.com/acquia/blt/pull/1365) ([grasmash](https://github.com/grasmash))
- Fixes \#1363 - Currently not possibl to hook into post deploy [\#1364](https://github.com/acquia/blt/pull/1364) ([kylebrowning](https://github.com/kylebrowning))
- Execute all VM commands from repo root. [\#1362](https://github.com/acquia/blt/pull/1362) ([grasmash](https://github.com/grasmash))

**Fixed bugs:**

- Disable update module at install time. [\#1360](https://github.com/acquia/blt/pull/1360) ([danepowell](https://github.com/danepowell))

**Closed issues:**

- Currently not possible to hook into post deploy [\#1363](https://github.com/acquia/blt/issues/1363)


## [8.8.0](https://github.com/acquia/blt/tree/8.8.0) (2017-04-11)
[Full Changelog](https://github.com/acquia/blt/compare/8.7.1...8.8.0)

**Implemented enhancements:**

- Fixes #1355: Make Drush alias for 'local' work in VM guest or host by default. [\#1356](https://github.com/acquia/blt/pull/1356)
- Support CDEs for config splits [\#1311](https://github.com/acquia/blt/issues/1311)
- Deploy can be run with uncommitted changes [\#1276](https://github.com/acquia/blt/issues/1276)
- Let phing recognize git environment variables [\#1249](https://github.com/acquia/blt/issues/1249)
- Allow deploy-exclude-additions.txt to be defined [\#1246](https://github.com/acquia/blt/issues/1246)
- Force tests:behat to be executed inside VM, if DVM is used. [\#1359](https://github.com/acquia/blt/pull/1359) ([grasmash](https://github.com/grasmash))
- Update notifications not disabled during Drupal 8.3.x installation [\#1353](https://github.com/acquia/blt/pull/1353) ([danepowell](https://github.com/danepowell))
- Add QA Accounts module [\#1351](https://github.com/acquia/blt/pull/1351) ([danepowell](https://github.com/danepowell))
- Fixes \#1276: Fail deploys with uncommitted changes. [\#1348](https://github.com/acquia/blt/pull/1348) ([bobbygryzynger](https://github.com/bobbygryzynger))
- Running Robo tests during internal CI. [\#1347](https://github.com/acquia/blt/pull/1347) ([grasmash](https://github.com/grasmash))
- Porting vm:\* commands to Robo. [\#1337](https://github.com/acquia/blt/pull/1337) ([grasmash](https://github.com/grasmash))
- Fix local warnings about trusted host pattern [\#1335](https://github.com/acquia/blt/pull/1335) ([danepowell](https://github.com/danepowell))
- Removing search\_api from suggested modules. [\#1334](https://github.com/acquia/blt/pull/1334) ([grasmash](https://github.com/grasmash))
- Fixes \#1317: Change doctrine/common to ^2.5 after Drupal 8.3.\* released. [\#1333](https://github.com/acquia/blt/pull/1333) ([grasmash](https://github.com/grasmash))
- Fixes \#1322: Examples.feature changes overwritten whenever BLT is updated. [\#1332](https://github.com/acquia/blt/pull/1332) ([grasmash](https://github.com/grasmash))
- Fixes \#1324: Add ah\_other config split. [\#1331](https://github.com/acquia/blt/pull/1331) ([grasmash](https://github.com/grasmash))
- Fixes \#1246: Allow deploy-exclude-additions.txt to be defined [\#1327](https://github.com/acquia/blt/pull/1327) ([malikkotob](https://github.com/malikkotob))

**Fixed bugs:**

- Travis email error starting with Drupal 8.3.0 and Lightning 2.1.0 [\#1336](https://github.com/acquia/blt/issues/1336)
- manual build and deploy process fails if git user.useConfigOnly option is set [\#53](https://github.com/acquia/blt/issues/53)
- Fix broken core-only config strategy [\#1358](https://github.com/acquia/blt/pull/1358) ([bkosborne](https://github.com/bkosborne))
- Fixes \#1336: Travis email error starting with Drupal 8.3.0 and Lightning 2.1.0. [\#1338](https://github.com/acquia/blt/pull/1338) ([grasmash](https://github.com/grasmash))

**Closed issues:**

- What to do if using Config Split with the RA environment? [\#1324](https://github.com/acquia/blt/issues/1324)
- Examples.feature changes overwritten whenever BLT is updated [\#1322](https://github.com/acquia/blt/issues/1322)
- Change doctrine/common to ^2.5 after Drupal 8.3.\* released [\#1317](https://github.com/acquia/blt/issues/1317)
- Complete Branching and Tagging documentation [\#1257](https://github.com/acquia/blt/issues/1257)

**Misc merged pull requests**

- Update configuration-management.md [\#1352](https://github.com/acquia/blt/pull/1352) ([danepowell](https://github.com/danepowell))
- Issue \#53: Added support for custom git username and email [\#1323](https://github.com/acquia/blt/pull/1323) ([danepowell](https://github.com/danepowell))
- Fixes \#1257 Complete branching and tagging documentation [\#1321](https://github.com/acquia/blt/pull/1321) ([malikkotob](https://github.com/malikkotob))
- Minor cleanup of configuration-management.md [\#1319](https://github.com/acquia/blt/pull/1319) ([danepowell](https://github.com/danepowell))


## [8.7.1](https://github.com/acquia/blt/tree/8.7.1) (2017-04-05)
[Full Changelog](https://github.com/acquia/blt/compare/8.7.0...8.7.1)

- Removed drupal/console from composer.required.json.


## [8.7.0](https://github.com/acquia/blt/tree/8.7.0) (2017-04-05)
[Full Changelog](https://github.com/acquia/blt/compare/8.7.0-beta4...8.7.0)

**Fixed bugs:**

- Ensuring that enable-patching remains in root composer.json. [\#1315](https://github.com/acquia/blt/pull/1315) ([grasmash](https://github.com/grasmash))

**Misc merged pull requests**

- Updating config split docs. [\#1316](https://github.com/acquia/blt/pull/1316) ([grasmash](https://github.com/grasmash))


## [8.7.0-beta4](https://github.com/acquia/blt/tree/8.7.0-beta4) (2017-04-04)
[Full Changelog](https://github.com/acquia/blt/compare/8.7.0-beta3...8.7.0-beta4)

**Implemented enhancements:**

- Standardizing config split stage name [\#1314](https://github.com/acquia/blt/pull/1314) ([grasmash](https://github.com/grasmash))
- Fixing split import for CI. [\#1312](https://github.com/acquia/blt/pull/1312) ([grasmash](https://github.com/grasmash))
- Changing include order of cloud settings files. [\#1310](https://github.com/acquia/blt/pull/1310) ([grasmash](https://github.com/grasmash))
- BLT-1304: Fixes \#1304 default no cache setting for SAML. [\#1306](https://github.com/acquia/blt/pull/1306) ([kylebrowning](https://github.com/kylebrowning))

**Fixed bugs:**

- $BLT\_DIR/scripts/travis/deploy\_branch running on TravisCI results in error [\#1308](https://github.com/acquia/blt/issues/1308)
- Running blt deploy:drupal:install -Denvironment=$target\_env -Dblt.verbose=true on cloud results in error [\#1307](https://github.com/acquia/blt/issues/1307)
- SimpleSAMLPHP defines NO\_CACHE by default. [\#1304](https://github.com/acquia/blt/issues/1304)
- Final setup:config-import:config-split not being run on blt local:setup [\#1303](https://github.com/acquia/blt/issues/1303)
- Fixes \#1307: Specify sync config directory for config import. [\#1309](https://github.com/acquia/blt/pull/1309) ([arknoll](https://github.com/arknoll))
- Fixes \#1303: config split import test. [\#1305](https://github.com/acquia/blt/pull/1305) ([arknoll](https://github.com/arknoll))


## [8.7.0-beta3](https://github.com/acquia/blt/tree/8.7.0-beta3) (2017-04-03)
[Full Changelog](https://github.com/acquia/blt/compare/8.7.0-beta2...8.7.0-beta3)

**Implemented enhancements:**

- Loading databases for each multisite [\#1227](https://github.com/acquia/blt/issues/1227)
- Don't run drush updb with the entity-updates flag during setup:config-import, it's dangerous [\#1014](https://github.com/acquia/blt/issues/1014)
- Connects to \#678: Set config sync dir correctly, despite ACE defaults. [\#1299](https://github.com/acquia/blt/pull/1299) ([grasmash](https://github.com/grasmash))
- Fixes \#1014: Don't run drush entity-updates during config-import. [\#1293](https://github.com/acquia/blt/pull/1293) ([grasmash](https://github.com/grasmash))
- Temporarily pinning to search\_api 1.0-beta4. [\#1291](https://github.com/acquia/blt/pull/1291) ([grasmash](https://github.com/grasmash))
- Fixes \#1273: Set web driver to Phantom js in vm:init. [\#1290](https://github.com/acquia/blt/pull/1290) ([grasmash](https://github.com/grasmash))
- Fixes \#1286: Silence git output when it may output fatal errors. [\#1289](https://github.com/acquia/blt/pull/1289) ([grasmash](https://github.com/grasmash))
- Fixes \#1227: Add target to sync all local multisite dbs. [\#1229](https://github.com/acquia/blt/pull/1229) ([grasmash](https://github.com/grasmash))

**Fixed bugs:**

- Travis deploy\_branch failing on 8.7.0-beta2 [\#1300](https://github.com/acquia/blt/issues/1300)
- composer merge + composer patches not playing nicely [\#1292](https://github.com/acquia/blt/issues/1292)
- Drupal install fails with error when using VM [\#1283](https://github.com/acquia/blt/issues/1283)
- Fixes \#1300: Travis deploy\_branch failing on 8.7.0-beta2. [\#1301](https://github.com/acquia/blt/pull/1301) ([grasmash](https://github.com/grasmash))
- Fix undefined index when running remote drush commands [\#1295](https://github.com/acquia/blt/pull/1295) ([danepowell](https://github.com/danepowell))
- Fixes \#1292: composer merge + composer patches not playing nicely. [\#1294](https://github.com/acquia/blt/pull/1294) ([grasmash](https://github.com/grasmash))
- Fixes \#1283: Drupal install fails with error when using VM [\#1288](https://github.com/acquia/blt/pull/1288) ([grasmash](https://github.com/grasmash))

**Closed issues:**

- Latest BLT causes local:refresh to fail [\#1284](https://github.com/acquia/blt/issues/1284)
- Set web driver to Phantom js in vm:init [\#1273](https://github.com/acquia/blt/issues/1273)

**Misc merged pull requests**

- Revert "Fixes \#1286: Silence git output when it may output fatal errors." [\#1296](https://github.com/acquia/blt/pull/1296) ([grasmash](https://github.com/grasmash))
- Moving composer repos to root composer.json. [\#1287](https://github.com/acquia/blt/pull/1287) ([grasmash](https://github.com/grasmash))
- Fixes \#1284: Local refresh db sync target needs to account for Drush alias. [\#1285](https://github.com/acquia/blt/pull/1285) ([geerlingguy](https://github.com/geerlingguy))
- Trying to fix BLT doctor for when Drupal is not installed. [\#1254](https://github.com/acquia/blt/pull/1254) ([grasmash](https://github.com/grasmash))


## [8.7.0-beta2](https://github.com/acquia/blt/tree/8.7.0-beta2) (2017-03-31)
[Full Changelog](https://github.com/acquia/blt/compare/8.7.0-beta1...8.7.0-beta2)

**Implemented enhancements:**

- Skip config import if config directory is empty [\#1272](https://github.com/acquia/blt/issues/1272)
- Make post-provision.sh script less opinionated [\#1264](https://github.com/acquia/blt/issues/1264)
- deploy:drupal:install doesn't work with config management [\#1247](https://github.com/acquia/blt/issues/1247)
- DrupalConsoleTask.php [\#1237](https://github.com/acquia/blt/issues/1237)
- Scripts called from BLT's hooks don't have access to internal variables [\#1232](https://github.com/acquia/blt/issues/1232)
- Add default patch for memcache to permit ODE integration [\#1224](https://github.com/acquia/blt/issues/1224)
- Adjust BLT's Config Split workflow for new Config Filter approach [\#1138](https://github.com/acquia/blt/issues/1138)
- Git pre-commit hook is slow [\#1104](https://github.com/acquia/blt/issues/1104)
- Connects to \#1028: Auto-discovery of $site\_dir [\#1086](https://github.com/acquia/blt/issues/1086)
- Multisite setup improvements [\#1028](https://github.com/acquia/blt/issues/1028)
- Fixes \#1138: Support Config Split for environment-specific Core CMI [\#965](https://github.com/acquia/blt/issues/965)
- .gitignore customizations overridden during BLT upgrade [\#915](https://github.com/acquia/blt/issues/915)
- Cloud hooks for ACSF [\#853](https://github.com/acquia/blt/issues/853)
- Make deployments fail when composer patches do not apply [\#705](https://github.com/acquia/blt/issues/705)
- Add node\_modules and bower\_components to yaml fileset excludes. [\#1280](https://github.com/acquia/blt/pull/1280) ([devert](https://github.com/devert))
- Add additional output during deployments. [\#1277](https://github.com/acquia/blt/pull/1277) ([bobbygryzynger](https://github.com/bobbygryzynger))
- Checking for config prior to import. [\#1275](https://github.com/acquia/blt/pull/1275) ([grasmash](https://github.com/grasmash))
- Instruct local files sync to exclude image styles as well as the css/js cache folders [\#1267](https://github.com/acquia/blt/pull/1267) ([bkosborne](https://github.com/bkosborne))
- Fixes \#1264: Make post-provision.sh script more flexible. [\#1266](https://github.com/acquia/blt/pull/1266) ([geerlingguy](https://github.com/geerlingguy))
- Fixes \#915: Sync drushrc.php with template drushrc.php via update hook. [\#1265](https://github.com/acquia/blt/pull/1265) ([malikkotob](https://github.com/malikkotob))
- Connects to \#915: Move config from template drushrc into vendor drushrc. [\#1261](https://github.com/acquia/blt/pull/1261) ([malikkotob](https://github.com/malikkotob))
- Connects to \#915: .gitignore customizations overridden. [\#1256](https://github.com/acquia/blt/pull/1256) ([malikkotob](https://github.com/malikkotob))
- Moving drush files from template into vendor. [\#1253](https://github.com/acquia/blt/pull/1253) ([grasmash](https://github.com/grasmash))
- Changing config management to be opt-in. [\#1251](https://github.com/acquia/blt/pull/1251) ([grasmash](https://github.com/grasmash))
- Add \*.tgz directive to .gitattributes [\#1248](https://github.com/acquia/blt/pull/1248) ([TravisCarden](https://github.com/TravisCarden))
- Fixes \#1104: Git pre-commit hook is slow. [\#1242](https://github.com/acquia/blt/pull/1242) ([malikkotob](https://github.com/malikkotob))
- Update .gitattributes \(Woff files are binary\) [\#1238](https://github.com/acquia/blt/pull/1238) ([danepowell](https://github.com/danepowell))
- Fixes \#1224: Add patch for memcache SASL Support, supports ODEs. [\#1230](https://github.com/acquia/blt/pull/1230) ([grasmash](https://github.com/grasmash))
- Fixes \#1225: Move cweagans/composer-patches to composer.required.json. [\#1226](https://github.com/acquia/blt/pull/1226) ([grasmash](https://github.com/grasmash))
- Adding blt/composer.overrides.json. [\#1221](https://github.com/acquia/blt/pull/1221) ([grasmash](https://github.com/grasmash))
- Fixes \#1212: Make Acquia Cloud hooks opt-in. [\#1219](https://github.com/acquia/blt/pull/1219) ([malikkotob](https://github.com/malikkotob))
- Fix Selenium failure due to insufficient entropy [\#1211](https://github.com/acquia/blt/pull/1211) ([fiasco](https://github.com/fiasco))
- Add Cloud hooks for ACSF  [\#1209](https://github.com/acquia/blt/pull/1209) ([lcatlett](https://github.com/lcatlett))
- Fixes \#705: Make deployments fail when composer patches do not apply. [\#1205](https://github.com/acquia/blt/pull/1205) ([grasmash](https://github.com/grasmash))
- Adding support for config\_split. [\#1102](https://github.com/acquia/blt/pull/1102) ([grasmash](https://github.com/grasmash))

**Fixed bugs:**

- Default project.yml of blt suggests to work with devel module that is not present in the file system [\#1258](https://github.com/acquia/blt/issues/1258)
- Build fails on blt vm:nuke if nuked previously [\#1213](https://github.com/acquia/blt/issues/1213)
- Fixing mixed up run\_tests scripts. [\#1282](https://github.com/acquia/blt/pull/1282) ([grasmash](https://github.com/grasmash))
- Fixing deploy:update target. [\#1281](https://github.com/acquia/blt/pull/1281) ([grasmash](https://github.com/grasmash))
- Fixing post-provision script for Drupal VM. [\#1274](https://github.com/acquia/blt/pull/1274) ([grasmash](https://github.com/grasmash))
- Fixes \#1258: Move devel to require in composer.required.json [\#1270](https://github.com/acquia/blt/pull/1270) ([malikkotob](https://github.com/malikkotob))
- Fixes \#1247: deploy:drupal:install doesn't work with config management [\#1268](https://github.com/acquia/blt/pull/1268) ([malikkotob](https://github.com/malikkotob))
- Fixes \#1262: Set Drupal VM's ssh\_home. [\#1263](https://github.com/acquia/blt/pull/1263) ([geerlingguy](https://github.com/geerlingguy))
- Fixing update hook version for 8.7.0. [\#1252](https://github.com/acquia/blt/pull/1252) ([grasmash](https://github.com/grasmash))
- Fixes \#1243: deployment failures caused by config\_split. [\#1244](https://github.com/acquia/blt/pull/1244) ([grasmash](https://github.com/grasmash))
- Fixes \#1235: Pipelines builds failing due to composer self-update [\#1236](https://github.com/acquia/blt/pull/1236) ([geerlingguy](https://github.com/geerlingguy))
- Working around compser-merge-plugin replace bug. [\#1233](https://github.com/acquia/blt/pull/1233) ([grasmash](https://github.com/grasmash))
- Fixes \#1215: Projects can't define post\_provision\_tasks\_dir. [\#1231](https://github.com/acquia/blt/pull/1231) ([grasmash](https://github.com/grasmash))
- Fix acquia/lightning version constraint. [\#1228](https://github.com/acquia/blt/pull/1228) ([grasmash](https://github.com/grasmash))
- Fixing PhantomJS installer version constraint, removing operators. [\#1223](https://github.com/acquia/blt/pull/1223) ([grasmash](https://github.com/grasmash))
- Making sites/\[site-name\] writable for setup:drush:settings target. [\#1222](https://github.com/acquia/blt/pull/1222) ([grasmash](https://github.com/grasmash))
- Fixes \#1213: Build fails on blt vm:nuke if nuked previously. [\#1217](https://github.com/acquia/blt/pull/1217) ([grasmash](https://github.com/grasmash))

**Closed issues:**

- Exclude node\_modules and bower\_components from validate:yaml [\#1279](https://github.com/acquia/blt/issues/1279)
- Deploy can be run with uncommitted changes [\#1276](https://github.com/acquia/blt/issues/1276)
- Set Drupal VM's 'ssh\_home' [\#1262](https://github.com/acquia/blt/issues/1262)
- Deployment failures caused by config\_split [\#1243](https://github.com/acquia/blt/issues/1243)
- Pipelines builds failing due to `composer self-update` [\#1235](https://github.com/acquia/blt/issues/1235)
- Move cweagans/composer-patches to composer.required.json. [\#1225](https://github.com/acquia/blt/issues/1225)
- Update acsf-setup.md with subprofile guidance instead of lightning.extend.yml [\#1218](https://github.com/acquia/blt/issues/1218)
- Make Acquia Cloud hooks opt-in [\#1212](https://github.com/acquia/blt/issues/1212)
- Behat failures with Selenium [\#1210](https://github.com/acquia/blt/issues/1210)

**Misc merged pull requests**

- Fixes \#1276: Ensure there are no uncommitted changes before deploy. [\#1278](https://github.com/acquia/blt/pull/1278) ([bobbygryzynger](https://github.com/bobbygryzynger))
- Fixes \#1218: Update acsf-setup.md with subprofile guidance. [\#1271](https://github.com/acquia/blt/pull/1271) ([malikkotob](https://github.com/malikkotob))
- Replaces \#1132: Allow project.prefix to be overridden. [\#1259](https://github.com/acquia/blt/pull/1259) ([bobbygryzynger](https://github.com/bobbygryzynger))
- Fixes instructions for configuring authsources and remote idp files. [\#1255](https://github.com/acquia/blt/pull/1255) ([dooleymatt](https://github.com/dooleymatt))
- Update config split documentation [\#1240](https://github.com/acquia/blt/pull/1240) ([danepowell](https://github.com/danepowell))
- Fixes \#1086: Auto-discovery of $site\_dir [\#1239](https://github.com/acquia/blt/pull/1239) ([malikkotob](https://github.com/malikkotob))
- Documenting post-drupal-scaffold-cmd to applying patches [\#1208](https://github.com/acquia/blt/pull/1208) ([justinlevi](https://github.com/justinlevi))
- Update mkdocs.yml with multisite docs [\#1207](https://github.com/acquia/blt/pull/1207) ([danepowell](https://github.com/danepowell))
- Fixing ignore-existing.txt values. [\#1206](https://github.com/acquia/blt/pull/1206) ([grasmash](https://github.com/grasmash))


## [8.7.0-beta1](https://github.com/acquia/blt/tree/8.7.0-beta1) (2017-03-16)
[Full Changelog](https://github.com/acquia/blt/compare/8.6.15...8.7.0-beta1)

**Implemented enhancements:**

- AC Cloud hook error related to slack settings [\#1176](https://github.com/acquia/blt/issues/1176)
- Exclude patches from merge? [\#1074](https://github.com/acquia/blt/issues/1074)
- BLT & Visual Regression Strategy [\#1072](https://github.com/acquia/blt/issues/1072)
- Require blt validate:phpcbf [\#977](https://github.com/acquia/blt/issues/977)
- Clear Twig caches on deployment [\#813](https://github.com/acquia/blt/issues/813)
- Improving workflow for adding BLT to existing projects. [\#1204](https://github.com/acquia/blt/pull/1204) ([grasmash](https://github.com/grasmash))
- Updating grasmash/yaml-cli and dfly/dot-access-data. [\#1203](https://github.com/acquia/blt/pull/1203) ([grasmash](https://github.com/grasmash))
- Splitting suggested composer packages from required. [\#1197](https://github.com/acquia/blt/pull/1197) ([grasmash](https://github.com/grasmash))
- Changing BLT internal testing to re-install Drupal on deploys to AC. [\#1187](https://github.com/acquia/blt/pull/1187) ([grasmash](https://github.com/grasmash))
- Adding validation for installers-path. [\#1186](https://github.com/acquia/blt/pull/1186) ([grasmash](https://github.com/grasmash))
- Add post-config-import hook [\#1185](https://github.com/acquia/blt/pull/1185) ([rjgwiz](https://github.com/rjgwiz))
- Changing Updater to use ints rather than semver. [\#1181](https://github.com/acquia/blt/pull/1181) ([grasmash](https://github.com/grasmash))
- Fixes \#1176: AC Cloud hook error related to slack settings . [\#1179](https://github.com/acquia/blt/pull/1179) ([grasmash](https://github.com/grasmash))
- Changing default composer config to use wikimedia/composer-merge-plugin. [\#1165](https://github.com/acquia/blt/pull/1165) ([grasmash](https://github.com/grasmash))
- Issue \#813: Clear twig caches on deployments. [\#1151](https://github.com/acquia/blt/pull/1151) ([danepowell](https://github.com/danepowell))
- Add file sync to local:sync. [\#1147](https://github.com/acquia/blt/pull/1147) ([bobbygryzynger](https://github.com/bobbygryzynger))

**Fixed bugs:**

- Add name property to installer-paths for type:drupal-library. [\#1183](https://github.com/acquia/blt/issues/1183)
- ACSF site verify failed [\#1182](https://github.com/acquia/blt/issues/1182)
- Cloud hook failure [\#1177](https://github.com/acquia/blt/issues/1177)
- Changing deploy:update to loop through multisite array. [\#1200](https://github.com/acquia/blt/pull/1200) ([grasmash](https://github.com/grasmash))
- Adding back composer munge for blt:create target. [\#1189](https://github.com/acquia/blt/pull/1189) ([grasmash](https://github.com/grasmash))
- Fixes \#1180, \#1182: BLT should ignore sites/g entirely. [\#1188](https://github.com/acquia/blt/pull/1188) ([grasmash](https://github.com/grasmash))
- 1183: Add name property to installer-paths for type:drupal-library. [\#1184](https://github.com/acquia/blt/pull/1184) ([greylabel](https://github.com/greylabel))
- Fixing artifact generated by BLT during CI. [\#1175](https://github.com/acquia/blt/pull/1175) ([grasmash](https://github.com/grasmash))

**Closed issues:**

- Improve Documentation to Address Recommended Developer Skillset [\#881](https://github.com/acquia/blt/issues/881)
- Manual steps required after old Bolt/BLT upgrade to 8.3.0/latest [\#858](https://github.com/acquia/blt/issues/858)

**Misc merged pull requests**

- Fixes \#858: Updating docs for non-composer to composer update. [\#1198](https://github.com/acquia/blt/pull/1198) ([grasmash](https://github.com/grasmash))
- BLT-881: additional formatting cleanup [\#1196](https://github.com/acquia/blt/pull/1196) ([mikemadison13](https://github.com/mikemadison13))
- BLT-881: cleaning up formatting [\#1195](https://github.com/acquia/blt/pull/1195) ([mikemadison13](https://github.com/mikemadison13))
- BLT-881: correcting yml formatting issue. [\#1194](https://github.com/acquia/blt/pull/1194) ([mikemadison13](https://github.com/mikemadison13))
- BLT-881: adding skills.md to mkdocs.yml. [\#1193](https://github.com/acquia/blt/pull/1193) ([mikemadison13](https://github.com/mikemadison13))
- Update mkdocs.yml [\#1192](https://github.com/acquia/blt/pull/1192) ([danepowell](https://github.com/danepowell))
- Update configuration management documentation [\#1154](https://github.com/acquia/blt/pull/1154) ([danepowell](https://github.com/danepowell))
- BLT-881: initial cut at developer skills documentation [\#902](https://github.com/acquia/blt/pull/902) ([mikemadison13](https://github.com/mikemadison13))


## [8.6.15](https://github.com/acquia/blt/tree/8.6.15) (2017-03-10)
[Full Changelog](https://github.com/acquia/blt/compare/8.6.14...8.6.15)

**Implemented enhancements:**

- Add vagrant\_ip to Drupal VM config.yml when running 'blt vm' [\#1112](https://github.com/acquia/blt/issues/1112)
- Exclude node\_modules from validate:twig [\#1110](https://github.com/acquia/blt/issues/1110)
- Auto-discovery of multisite.name [\#1087](https://github.com/acquia/blt/issues/1087)
- Test for schema changes to stored config [\#842](https://github.com/acquia/blt/issues/842)
- Automate / allow overrides of .htaccess for SSL and SAML [\#608](https://github.com/acquia/blt/issues/608)
- Fixes \#1173: Update Drupal VM to 4.3.0 or later. [\#1174](https://github.com/acquia/blt/pull/1174) ([geerlingguy](https://github.com/geerlingguy))
- Creating temporary build branch name when tags are built. [\#1171](https://github.com/acquia/blt/pull/1171) ([grasmash](https://github.com/grasmash))
- Fixes \#1110: Exclude node\_modules from validate:twig. [\#1168](https://github.com/acquia/blt/pull/1168) ([grasmash](https://github.com/grasmash))
- Fixes \#1152: Allow for screenshots in Behat test runs. [\#1158](https://github.com/acquia/blt/pull/1158) ([geerlingguy](https://github.com/geerlingguy))
- Fixes \#1141: Fixes deploy updates only applied to default site. [\#1146](https://github.com/acquia/blt/pull/1146) ([danepowell](https://github.com/danepowell))
- Added node modules to the php linting excludes list. [\#1143](https://github.com/acquia/blt/pull/1143) ([aweingarten](https://github.com/aweingarten))
- Removing nvm from blt CI scripts. [\#1142](https://github.com/acquia/blt/pull/1142) ([grasmash](https://github.com/grasmash))
- Add local:sync:files task. [\#1136](https://github.com/acquia/blt/pull/1136) ([bobbygryzynger](https://github.com/bobbygryzynger))
- Fixes \#1112: Add a default vagrant\_ip and comment in Drupal VM config.yml. Fixes \#1112. [\#1130](https://github.com/acquia/blt/pull/1130) ([geerlingguy](https://github.com/geerlingguy))
- Connects to \#1113: Add multisite properties documentation. [\#1124](https://github.com/acquia/blt/pull/1124) ([bobbygryzynger](https://github.com/bobbygryzynger))
- Fixes \#1087: Auto-discovery of multisite.name. [\#1119](https://github.com/acquia/blt/pull/1119) ([bobbygryzynger](https://github.com/bobbygryzynger))
- Feature/blt phpcbf [\#1019](https://github.com/acquia/blt/pull/1019) ([dooleymatt](https://github.com/dooleymatt))

**Fixed bugs:**

- BLT incorrectly sets file\_private\_path for Site Factory [\#1160](https://github.com/acquia/blt/issues/1160)
- Drush aliases file not correctly generated on blt vm if nuked before [\#1155](https://github.com/acquia/blt/issues/1155)
- Automated tag deployments on Travis CI fail with 'src refspec matches more than one` [\#1150](https://github.com/acquia/blt/issues/1150)
- Deploy updates not applied to multisites [\#1141](https://github.com/acquia/blt/issues/1141)
- PHP Notice:  Undefined index: gardens\_site\_settings [\#1038](https://github.com/acquia/blt/issues/1038)
- Fixing deployment on BLTed8 sandbox. [\#1172](https://github.com/acquia/blt/pull/1172) ([grasmash](https://github.com/grasmash))
- Properly setting multisite.name during create-project phase. [\#1170](https://github.com/acquia/blt/pull/1170) ([grasmash](https://github.com/grasmash))
- Adding known hosts to blted8 sandbox. [\#1169](https://github.com/acquia/blt/pull/1169) ([grasmash](https://github.com/grasmash))
- Fixes bug multisite generation of local.drushrc.php. [\#1167](https://github.com/acquia/blt/pull/1167) ([grasmash](https://github.com/grasmash))
- Fixes \#1150: Automated tag deployments on Travis CI fail with `src refspec matches more than one` [\#1166](https://github.com/acquia/blt/pull/1166) ([grasmash](https://github.com/grasmash))
- Fixes \#1155: Drush aliases file not correctly generated on blt vm if nuked before. [\#1162](https://github.com/acquia/blt/pull/1162) ([grasmash](https://github.com/grasmash))
- Fixes \#1160: BLT incorrectly sets file\_private\_path for Site Factory. [\#1161](https://github.com/acquia/blt/pull/1161) ([grasmash](https://github.com/grasmash))
- Fixes \#1148: update cm keys in deploy.xml. [\#1149](https://github.com/acquia/blt/pull/1149) ([bobbygryzynger](https://github.com/bobbygryzynger))

**Closed issues:**

- Update Drupal VM requirement to ^4.3 [\#1173](https://github.com/acquia/blt/issues/1173)
- Add ability to save screenshot and/or dump of html for failed behat tests. [\#1152](https://github.com/acquia/blt/issues/1152)
- Configuration keys incorrectly set in deploy.xml [\#1148](https://github.com/acquia/blt/issues/1148)
- Can't get tag to deploy via Travis CI [\#1137](https://github.com/acquia/blt/issues/1137)

**Misc merged pull requests**

- Issue \#1152 follow-up: Add docs for Behat ScreenshotExtension. [\#1164](https://github.com/acquia/blt/pull/1164) ([geerlingguy](https://github.com/geerlingguy))
- Issue \#608: Improved patch documentation. [\#1157](https://github.com/acquia/blt/pull/1157) ([danepowell](https://github.com/danepowell))
- Added additional Windows gotcha related to missing/hidden files. [\#1144](https://github.com/acquia/blt/pull/1144) ([ashabed](https://github.com/ashabed))
- Fix misleading "Your composer.json file was modified by BLT" error message [\#1139](https://github.com/acquia/blt/pull/1139) ([TravisCarden](https://github.com/TravisCarden))
- Update multisite.md [\#1129](https://github.com/acquia/blt/pull/1129) ([danepowell](https://github.com/danepowell))
- Increasing timeout for Symfony processes created by Composer. [\#1115](https://github.com/acquia/blt/pull/1115) ([grasmash](https://github.com/grasmash))


## [8.6.13](https://github.com/acquia/blt/tree/8.6.13) (2017-02-17)
[Full Changelog](https://github.com/acquia/blt/compare/8.6.11...8.6.13)

**Notable Changes**
- .travis.yml and acquia-pipelines.yml have been significantly refactored to reduce the maintenance effort moving forward. **You must re-initialize these files**. E.g.,
  - `rm .travis.yml && blt ci:travis:init`
  - `rm acquia-pipelines.yml && blt ci:pipelines:init`
- BLT no longer runs its own PHPUnit tests on child projects. An example PHPUnit test has been added for child projects to verify that tests do still run.

**Implemented enhancements:**
- DrushTask needs outputProperty instead of just returnProperty [#1088](https://github.com/acquia/blt/issues/1088)
- Make the cm.core.config-dir property configurable [#1013](https://github.com/acquia/blt/issues/1013)
- Allow skipping of BLT's own PHPUnit tests [#982](https://github.com/acquia/blt/issues/982)
- Make default acquia-pipelines.yml configuration distributable [#976](https://github.com/acquia/blt/issues/976)
- Support for multiple CM approaches. [#854](https://github.com/acquia/blt/issues/854)
- Improving internal testing of Pipelines [#1114](https://github.com/acquia/blt/pull/1114) ([grasmash](https://github.com/grasmash))
- Re-starting MySQL in acquia-pipelines.yml. [#1107](https://github.com/acquia/blt/pull/1107) ([grasmash](https://github.com/grasmash))
- Exclude acquia-pipelines.yml from artifact. [#1101](https://github.com/acquia/blt/pull/1101) ([grasmash](https://github.com/grasmash))
- Defining JDK version directly in .travis.yml. [#1100](https://github.com/acquia/blt/pull/1100) ([grasmash](https://github.com/grasmash))
- Pushing source 8.x branch to blted8 sandbox automatically. [#1099](https://github.com/acquia/blt/pull/1099) ([grasmash](https://github.com/grasmash))
- Adding PHPUnit bootstrap. [#1098](https://github.com/acquia/blt/pull/1098) ([grasmash](https://github.com/grasmash))
- Adapts #1024: Add pre-config-import hook [#1095](https://github.com/acquia/blt/pull/1095) ([bobbygryzynger](https://github.com/bobbygryzynger))
- Support for multisite deploys [#1092](https://github.com/acquia/blt/pull/1092) ([danepowell](https://github.com/danepowell))
- Fixes #1078, #1029, #1013, #966: Allow configuration directory and partial flag to be configurable. [#1080](https://github.com/acquia/blt/pull/1080) ([grasmash](https://github.com/grasmash))
- Fixing PHPCS filset reference. [#1079](https://github.com/acquia/blt/pull/1079) ([dpagini](https://github.com/dpagini))
- Fixes #1070: Adding variables for phpcs haltonerror and haltonwarning. [#1071](https://github.com/acquia/blt/pull/1071) ([grasmash](https://github.com/grasmash))
- Adding vm:nuke command. [#1069](https://github.com/acquia/blt/pull/1069) ([grasmash](https://github.com/grasmash))
- Creating example PHPUnit test. [#1068](https://github.com/acquia/blt/pull/1068) ([grasmash](https://github.com/grasmash))
- Adding composer-installers-extender. [#1064](https://github.com/acquia/blt/pull/1064) ([grasmash](https://github.com/grasmash))
- Adding installer paths for custom modules, themes, and profiles. [#1063](https://github.com/acquia/blt/pull/1063) ([grasmash](https://github.com/grasmash))
- Update lightning to 2.0.3 [#1054](https://github.com/acquia/blt/pull/1054) ([balsama](https://github.com/balsama))
- TravisCI MySQL errors [#1053](https://github.com/acquia/blt/pull/1053) ([danepowell](https://github.com/danepowell))
- Renaming locale to drupal.locale. [#1045](https://github.com/acquia/blt/pull/1045) ([grasmash](https://github.com/grasmash))
- Add locale option for site-install task. [#1043](https://github.com/acquia/blt/pull/1043) ([snize](https://github.com/snize))
- Refactoring BLT's internal CI scripts for Pipelines usage. [#1040](https://github.com/acquia/blt/pull/1040) ([grasmash](https://github.com/grasmash))
- Connects to #1032: Delete SettingsTest.php [#1037](https://github.com/acquia/blt/pull/1037) ([bobbygryzynger](https://github.com/bobbygryzynger))
- Splitting Travis instructions into separate bash files. [#1035](https://github.com/acquia/blt/pull/1035) ([grasmash](https://github.com/grasmash))
- Connects to  #1028: Add drush and drupal settings tests [#1032](https://github.com/acquia/blt/pull/1032) ([bobbygryzynger](https://github.com/bobbygryzynger))
- Moving drupal/console requirement to template composer.json. [#1031](https://github.com/acquia/blt/pull/1031) ([grasmash](https://github.com/grasmash))
- Adding file_exists() check to composer munge command. [#1030](https://github.com/acquia/blt/pull/1030) ([grasmash](https://github.com/grasmash))
- Fixes #985: Implement deploy_install() to rebuild projects on deploy. [#986](https://github.com/acquia/blt/pull/986) ([swichers](https://github.com/swichers))
- Add support for disabling targets from the local project override file. [#1077](https://github.com/acquia/blt/pull/1077) ([bkosborne](https://github.com/bkosborne))
- Run Cloud aliases in Cloud environments. [#1076](https://github.com/acquia/blt/pull/1076) ([danepowell](https://github.com/danepowell))
- Double-revert features [#1073](https://github.com/acquia/blt/pull/1073) ([danepowell](https://github.com/danepowell))

**Fixed bugs:**
- Behat tests failing on Pipelines in master/pulled 8.6.12 release [#1111](https://github.com/acquia/blt/issues/1111)
- .travis.yml attempts to run scripts that are not yet installed via composer. [#1094](https://github.com/acquia/blt/issues/1094)
- Pipelines jobs failing after upgrading to BLT 8.6.12 [#1093](https://github.com/acquia/blt/issues/1093)
- Missing space in .travis.yml causes travis-ci validation error. [#1090](https://github.com/acquia/blt/issues/1090)
- Tags can be blank which results in no tag being deployed. [#1065](https://github.com/acquia/blt/issues/1065)
- db_scrub script should not fire on ACSF [#1059](https://github.com/acquia/blt/issues/1059)
- It's not possible to disable targets via project.local.yml [#1052](https://github.com/acquia/blt/issues/1052)
- Build artifact .gitignore is blown away right after it's copied over [#1007](https://github.com/acquia/blt/issues/1007)
- Commit message needs to be escaped? [#1006](https://github.com/acquia/blt/issues/1006)
- Add missing newline to end of aliases.drushrc.php [#1103](https://github.com/acquia/blt/pull/1103) ([TravisCarden](https://github.com/TravisCarden))
- Adding source prefix to pipelines commands. [#1097](https://github.com/acquia/blt/pull/1097) ([grasmash](https://github.com/grasmash))
- 1090: add space to .travis.yml so it validates [#1091](https://github.com/acquia/blt/pull/1091) ([mariagwyn](https://github.com/mariagwyn))
- Fixes #1065: Tags can be blank which results in no tag being deployed. [#1067](https://github.com/acquia/blt/pull/1067) ([grasmash](https://github.com/grasmash))
- Fixes #1006: Commit message needs to be escaped. [#1062](https://github.com/acquia/blt/pull/1062) ([grasmash](https://github.com/grasmash))
- Fixes #1007: Build artifact .gitignore is blown away right after it's copied over [#1061](https://github.com/acquia/blt/pull/1061) ([grasmash](https://github.com/grasmash))
- Fixes #1059: db-scrub script should not fire on ACSF. [#1060](https://github.com/acquia/blt/pull/1060) ([grasmash](https://github.com/grasmash))
- Fixed multisite settings on ACE. [#1042](https://github.com/acquia/blt/pull/1042) ([danepowell](https://github.com/danepowell))

**Closed issues:**
- 'drupal-custom-module' in template needs to be prefixed with 'type:' [#1084](https://github.com/acquia/blt/issues/1084)
- Unprocessed replacement in default.local.drushrc.php [#1057](https://github.com/acquia/blt/issues/1057)
- Travis MySQL errors [#1036](https://github.com/acquia/blt/issues/1036)
-  jakoch/phantomjs-installer 2.1.1-p08 requires ext-bz2 * -> the requested PHP extension bz2 is missing from your system. [#1033](https://github.com/acquia/blt/issues/1033)
- Add a deploy_install command to reinstall projects on every deploy. [#985](https://github.com/acquia/blt/issues/985)
- Ansible needs to be at least 2.2 [#889](https://github.com/acquia/blt/issues/889)
- Issue 1084: add 'type:' to drupal-custom-module installer path. [#1085](https://github.com/acquia/blt/pull/1085) ([mariagwyn](https://github.com/mariagwyn))

**Misc merged pull requests**
- Updating RELEASE.md with status badges. [#1106](https://github.com/acquia/blt/pull/1106) ([grasmash](https://github.com/grasmash))
- Update ci.md [#1082](https://github.com/acquia/blt/pull/1082) ([danepowell](https://github.com/danepowell))
- Added notes on how to update modules with Features workflow [#1081](https://github.com/acquia/blt/pull/1081) ([danepowell](https://github.com/danepowell))
- Fixes #1057: Unprocessed replacement in default.local.drushrc.php [#1058](https://github.com/acquia/blt/pull/1058) ([grasmash](https://github.com/grasmash))
- Trivial Windows documentation change [#1056](https://github.com/acquia/blt/pull/1056) ([ashabed](https://github.com/ashabed))
- Update README.md [#1055](https://github.com/acquia/blt/pull/1055) ([ajitdev](https://github.com/ajitdev))
- More multisite documentation [#1049](https://github.com/acquia/blt/pull/1049) ([danepowell](https://github.com/danepowell))
- Document multisite setup process [#1027](https://github.com/acquia/blt/pull/1027) ([danepowell](https://github.com/danepowell))


## [8.6.12](https://github.com/acquia/blt/tree/8.6.12) (2017-02-13)
[Full Changelog](https://github.com/acquia/blt/compare/8.6.11...8.6.12)

**Implemented enhancements:**

- Allow skipping of BLT's own PHPUnit tests [\#982](https://github.com/acquia/blt/issues/982)
- Make default acquia-pipelines.yml configuration distributable [\#976](https://github.com/acquia/blt/issues/976)
- Fixes \#1078, \#1029, \#1013, \#966: Allow configuration directory and partial flag to be configurable. [\#1080](https://github.com/acquia/blt/pull/1080) ([grasmash](https://github.com/grasmash))
- Fixing PHPCS filset reference. [\#1079](https://github.com/acquia/blt/pull/1079) ([dpagini](https://github.com/dpagini))
- Fixes \#1070: Adding variables for phpcs haltonerror and haltonwarning. [\#1071](https://github.com/acquia/blt/pull/1071) ([grasmash](https://github.com/grasmash))
- Adding vm:nuke command. [\#1069](https://github.com/acquia/blt/pull/1069) ([grasmash](https://github.com/grasmash))
- Creating example PHPUnit test. [\#1068](https://github.com/acquia/blt/pull/1068) ([grasmash](https://github.com/grasmash))
- Adding composer-installers-extender. [\#1064](https://github.com/acquia/blt/pull/1064) ([grasmash](https://github.com/grasmash))
- Adding installer paths for custom modules, themes, and profiles. [\#1063](https://github.com/acquia/blt/pull/1063) ([grasmash](https://github.com/grasmash))
- Update lightning to 2.0.3 [\#1054](https://github.com/acquia/blt/pull/1054) ([balsama](https://github.com/balsama))
- TravisCI MySQL errors [\#1053](https://github.com/acquia/blt/pull/1053) ([danepowell](https://github.com/danepowell))
- Renaming locale to drupal.locale. [\#1045](https://github.com/acquia/blt/pull/1045) ([grasmash](https://github.com/grasmash))
- Add locale option for site-install task. [\#1043](https://github.com/acquia/blt/pull/1043) ([snize](https://github.com/snize))
- Connects to \#1032: Delete SettingsTest.php [\#1037](https://github.com/acquia/blt/pull/1037) ([bobbygryzynger](https://github.com/bobbygryzynger))
- Splitting Travis instructions into separate bash files. [\#1035](https://github.com/acquia/blt/pull/1035) ([grasmash](https://github.com/grasmash))
- Connects to  \#1028: Add drush and drupal settings tests [\#1032](https://github.com/acquia/blt/pull/1032) ([bobbygryzynger](https://github.com/bobbygryzynger))
- Moving drupal/console requirement to template composer.json. [\#1031](https://github.com/acquia/blt/pull/1031) ([grasmash](https://github.com/grasmash))
- Adding file\_exists\(\) check to composer munge command. [\#1030](https://github.com/acquia/blt/pull/1030) ([grasmash](https://github.com/grasmash))
- Fixes \#985: Implement deploy\_install\(\) to rebuild projects on deploy. [\#986](https://github.com/acquia/blt/pull/986) ([swichers](https://github.com/swichers))

**Fixed bugs:**

- Tags can be blank which results in no tag being deployed. [\#1065](https://github.com/acquia/blt/issues/1065)
- db\_scrub script should not fire on ACSF [\#1059](https://github.com/acquia/blt/issues/1059)
- It's not possible to disable targets via project.local.yml [\#1052](https://github.com/acquia/blt/issues/1052)
- Build artifact .gitignore is blown away right after it's copied over [\#1007](https://github.com/acquia/blt/issues/1007)
- Commit message needs to be escaped? [\#1006](https://github.com/acquia/blt/issues/1006)
- Fixes \#1065: Tags can be blank which results in no tag being deployed. [\#1067](https://github.com/acquia/blt/pull/1067) ([grasmash](https://github.com/grasmash))
- Fixes \#1006: Commit message needs to be escaped. [\#1062](https://github.com/acquia/blt/pull/1062) ([grasmash](https://github.com/grasmash))
- Fixes \#1007: Build artifact .gitignore is blown away right after it's copied over [\#1061](https://github.com/acquia/blt/pull/1061) ([grasmash](https://github.com/grasmash))
- Fixes \#1059: db-scrub script should not fire on ACSF. [\#1060](https://github.com/acquia/blt/pull/1060) ([grasmash](https://github.com/grasmash))
- Fixed multisite settings on ACE. [\#1042](https://github.com/acquia/blt/pull/1042) ([danepowell](https://github.com/danepowell))

**Closed issues:**

- Unprocessed replacement in default.local.drushrc.php [\#1057](https://github.com/acquia/blt/issues/1057)
- Travis MySQL errors [\#1036](https://github.com/acquia/blt/issues/1036)
-  jakoch/phantomjs-installer 2.1.1-p08 requires ext-bz2 \* -\> the requested PHP extension bz2 is missing from your system. [\#1033](https://github.com/acquia/blt/issues/1033)
- Add a deploy\_install command to reinstall projects on every deploy. [\#985](https://github.com/acquia/blt/issues/985)
- Ansible needs to be at least 2.2 [\#889](https://github.com/acquia/blt/issues/889)

**Misc merged pull requests**

- Add support for disabling targets from the local project override file. [\#1077](https://github.com/acquia/blt/pull/1077) ([bkosborne](https://github.com/bkosborne))
- Run Cloud aliases in Cloud environments. [\#1076](https://github.com/acquia/blt/pull/1076) ([danepowell](https://github.com/danepowell))
- Double-revert features [\#1073](https://github.com/acquia/blt/pull/1073) ([danepowell](https://github.com/danepowell))
- Fixes \#1057: Unprocessed replacement in default.local.drushrc.php [\#1058](https://github.com/acquia/blt/pull/1058) ([grasmash](https://github.com/grasmash))
- Trivial Windows documentation change [\#1056](https://github.com/acquia/blt/pull/1056) ([ashabed](https://github.com/ashabed))
- Update README.md [\#1055](https://github.com/acquia/blt/pull/1055) ([ajitdev](https://github.com/ajitdev))
- More multisite documentation [\#1049](https://github.com/acquia/blt/pull/1049) ([danepowell](https://github.com/danepowell))
- Document multisite setup process [\#1027](https://github.com/acquia/blt/pull/1027) ([danepowell](https://github.com/danepowell))

## [8.6.11](https://github.com/acquia/blt/tree/8.6.11) (2017-02-01)
[Full Changelog](https://github.com/acquia/blt/compare/8.6.10...8.6.11)

**Implemented enhancements:**

- Example BLT sandbox project that is kept up to date [\#847](https://github.com/acquia/blt/issues/847)
- blt function \(alias\) for fish [\#662](https://github.com/acquia/blt/issues/662)
- Disabling Lightning's Behat tests by default. [\#1022](https://github.com/acquia/blt/pull/1022) ([grasmash](https://github.com/grasmash))
- Fixes \#1017: Running BLT commands in Drupal VM requires bz2; update to Drupal VM 4.2.x. [\#1018](https://github.com/acquia/blt/pull/1018) ([geerlingguy](https://github.com/geerlingguy))
- Allow bin directory to be configurable [\#1001](https://github.com/acquia/blt/pull/1001) ([balsama](https://github.com/balsama))
- Slack notifications for deployments [\#1000](https://github.com/acquia/blt/pull/1000) ([danepowell](https://github.com/danepowell))
- Fixes \#885: Local & CI Tests Fail with Git Hooks Disabled. [\#998](https://github.com/acquia/blt/pull/998) ([shahinam](https://github.com/shahinam))
- Downgrade PHPUnit to version used by Drupal core \(~4.8\) [\#996](https://github.com/acquia/blt/pull/996) ([TravisCarden](https://github.com/TravisCarden))
- Fixes \#847: Pushing to GitHub blted8 sandbox after successful 8.x builds. [\#993](https://github.com/acquia/blt/pull/993) ([grasmash](https://github.com/grasmash))
- Customizing Robo output. [\#988](https://github.com/acquia/blt/pull/988) ([grasmash](https://github.com/grasmash))
- Tag names should behave like branches. [\#979](https://github.com/acquia/blt/pull/979) ([danepowell](https://github.com/danepowell))
- Ignore compiled theme CSS and other artifacts [\#978](https://github.com/acquia/blt/pull/978) ([danepowell](https://github.com/danepowell))
- Fixes \#963: behat.extra is not actually used. [\#970](https://github.com/acquia/blt/pull/970) ([grasmash](https://github.com/grasmash))
- Fixes \#888: Adding unit test for YamlMungeCommand. [\#961](https://github.com/acquia/blt/pull/961) ([grasmash](https://github.com/grasmash))
- Allow access to update.php locally. [\#958](https://github.com/acquia/blt/pull/958) ([danepowell](https://github.com/danepowell))
- Adding more interaction to blt:release. [\#956](https://github.com/acquia/blt/pull/956) ([grasmash](https://github.com/grasmash))
- Fixes \#953: Allow local database sanitization to be disabled. [\#954](https://github.com/acquia/blt/pull/954) ([grasmash](https://github.com/grasmash))
- Set PHP memory limit for Travis. [\#947](https://github.com/acquia/blt/pull/947) ([danepowell](https://github.com/danepowell))
- Adding prompt for booting VM. [\#945](https://github.com/acquia/blt/pull/945) ([grasmash](https://github.com/grasmash))

**Fixed bugs:**

- New behat.extra option is not actually passed along. [\#963](https://github.com/acquia/blt/issues/963)
- BLT \(\> 8.6.6\) only runnable from repo root [\#952](https://github.com/acquia/blt/issues/952)
- Local & CI Tests Fail with Git Hooks Disabled [\#885](https://github.com/acquia/blt/issues/885)
- Fixing build errors in BLT CI. [\#1021](https://github.com/acquia/blt/pull/1021) ([grasmash](https://github.com/grasmash))
- Fixes \#1011: provide correct path to phpunit tests [\#1012](https://github.com/acquia/blt/pull/1012) ([bobbygryzynger](https://github.com/bobbygryzynger))
- Fixed features override test. [\#1010](https://github.com/acquia/blt/pull/1010) ([danepowell](https://github.com/danepowell))
- Fixes \#983: Exclude project.local.yml from deploy builds. [\#984](https://github.com/acquia/blt/pull/984) ([swichers](https://github.com/swichers))
- Fixes \#980: Issue on SimpleSAMLphp init [\#981](https://github.com/acquia/blt/pull/981) ([dooleymatt](https://github.com/dooleymatt))
- Fixes \#967: Travis and xvfb. [\#971](https://github.com/acquia/blt/pull/971) ([grasmash](https://github.com/grasmash))
- Fixes \#968: Tag-based Travis deploys don't work. [\#969](https://github.com/acquia/blt/pull/969) ([grasmash](https://github.com/grasmash))

**Closed issues:**

- arknoll.selenium issue while running blt vm [\#1020](https://github.com/acquia/blt/issues/1020)
- Running BLT commands within Drupal VM requires bz2 extension [\#1017](https://github.com/acquia/blt/issues/1017)
- phpunit tests not executed during build [\#1011](https://github.com/acquia/blt/issues/1011)
- Add box/local.config.yml to gitignore \(for Drupal VM local overrides\) [\#997](https://github.com/acquia/blt/issues/997)
- Travis CI Drupal site install failing after 8.6.10 upgrade [\#990](https://github.com/acquia/blt/issues/990)
- deploy-exclude.txt is missing project.local.yml from listed excludes. [\#983](https://github.com/acquia/blt/issues/983)
- Issue on SimpleSAMLphp init [\#980](https://github.com/acquia/blt/issues/980)
- Tag-based Travis deploys don't work [\#968](https://github.com/acquia/blt/issues/968)
- Travis and xvfb [\#967](https://github.com/acquia/blt/issues/967)
- Allow local database sanitization to be disabled [\#953](https://github.com/acquia/blt/issues/953)
- Documentation: More comprehensive CM docs [\#846](https://github.com/acquia/blt/issues/846)

**Misc merged pull requests**

- Fixes .travis.yml typos [\#1015](https://github.com/acquia/blt/pull/1015) ([bobbygryzynger](https://github.com/bobbygryzynger))
- Update Acquia Cloud URL to new domain [\#1008](https://github.com/acquia/blt/pull/1008) ([christopher-hopper](https://github.com/christopher-hopper))
- Cleaned up features docs [\#1003](https://github.com/acquia/blt/pull/1003) ([danepowell](https://github.com/danepowell))
- Fixes grammar in code comment [\#994](https://github.com/acquia/blt/pull/994) ([jeffymahoney](https://github.com/jeffymahoney))


## [8.6.10](https://github.com/acquia/blt/tree/8.6.10) (2017-01-10)
[Full Changelog](https://github.com/acquia/blt/compare/8.6.9...8.6.10)

**Implemented enhancements:**

- YAML validation is too verbose [\#950](https://github.com/acquia/blt/issues/950)
- Add Pipelines to DrupalVM [\#870](https://github.com/acquia/blt/issues/870)
- Removing contexts from behat.yml. [\#949](https://github.com/acquia/blt/pull/949) ([grasmash](https://github.com/grasmash))
- Scripting releases. [\#948](https://github.com/acquia/blt/pull/948) ([grasmash](https://github.com/grasmash))
- Adding behat.extra property for arbitrary CLI args. [\#946](https://github.com/acquia/blt/pull/946) ([grasmash](https://github.com/grasmash))
- Reducing output of default PHPUnit tests. [\#944](https://github.com/acquia/blt/pull/944) ([grasmash](https://github.com/grasmash))
- Tweaking output to remove more passthru. [\#941](https://github.com/acquia/blt/pull/941) ([grasmash](https://github.com/grasmash))
- DB updates should always be run before config imports. [\#930](https://github.com/acquia/blt/pull/930) ([danepowell](https://github.com/danepowell))
- Fixes \#870: Add Pipelines binary to DrupalVM. [\#874](https://github.com/acquia/blt/pull/874) ([grasmash](https://github.com/grasmash))

**Fixed bugs:**

- Fixes \#940: Prevent tag prompt. [\#942](https://github.com/acquia/blt/pull/942) ([grasmash](https://github.com/grasmash))

**Closed issues:**

- When deploying, I'm always prompted to enter a tag name even if I chose not to create one [\#940](https://github.com/acquia/blt/issues/940)


## [8.6.9](https://github.com/acquia/blt/tree/8.6.9) (2017-01-06)
[Full Changelog](https://github.com/acquia/blt/compare/8.6.8...8.6.9)

**Implemented enhancements:**

- Silently killing processes. [\#937](https://github.com/acquia/blt/pull/937) ([grasmash](https://github.com/grasmash))
- Preventing warning about missing project.yml. [\#936](https://github.com/acquia/blt/pull/936) ([grasmash](https://github.com/grasmash))
- Disabling passthru for various commands. [\#934](https://github.com/acquia/blt/pull/934) ([grasmash](https://github.com/grasmash))
- Removing metatag req. [\#932](https://github.com/acquia/blt/pull/932) ([grasmash](https://github.com/grasmash))
- Fixing deploy prompts. [\#931](https://github.com/acquia/blt/pull/931) ([grasmash](https://github.com/grasmash))

**Fixed bugs:**

- Fixing BLT executable. [\#933](https://github.com/acquia/blt/pull/933) ([grasmash](https://github.com/grasmash))

## [8.6.8](https://github.com/acquia/blt/tree/8.6.8) (2017-01-06)
[Full Changelog](https://github.com/acquia/blt/compare/8.6.7...8.6.8)

Hotfix to project creation process [b013590](https://github.com/acquia/blt/commit/b013590e7f573044d9e9c174d8ba1edf5e5cac4f)

## [8.6.7](https://github.com/acquia/blt/tree/8.6.7) (2017-01-06)
[Full Changelog](https://github.com/acquia/blt/compare/8.6.6...8.6.7)

**Implemented enhancements:**

- Investigate Parallelizing Behat tests [\#900](https://github.com/acquia/blt/issues/900)
- Upgrade to Drupal VM 4.1.0 [\#898](https://github.com/acquia/blt/issues/898)
- Add YAML validation [\#857](https://github.com/acquia/blt/issues/857)
- Tagging integration with Travis CI / Pipelines? [\#830](https://github.com/acquia/blt/issues/830)
- Remove `factory-hooks` from default template [\#599](https://github.com/acquia/blt/issues/599)
- Upgrade Drupal Console to latest RC [\#513](https://github.com/acquia/blt/issues/513)
- Investigate replacing custom commands with drupal/console commands [\#251](https://github.com/acquia/blt/issues/251)
- Adding create-project:init-repo target. [\#928](https://github.com/acquia/blt/pull/928) ([grasmash](https://github.com/grasmash))
- Add inclusion of optional site settings file. [\#927](https://github.com/acquia/blt/pull/927) ([dpagini](https://github.com/dpagini))
- Remove factory-hooks from default template. [\#923](https://github.com/acquia/blt/pull/923) ([dpagini](https://github.com/dpagini))
- Adding custom BltLogger for Phing. [\#922](https://github.com/acquia/blt/pull/922) ([grasmash](https://github.com/grasmash))
- Delete trusted\_host.settings.php [\#921](https://github.com/acquia/blt/pull/921) ([danepowell](https://github.com/danepowell))
- Ignore private files. [\#920](https://github.com/acquia/blt/pull/920) ([danepowell](https://github.com/danepowell))
- Executing create-project on initial install using Composer plugin. [\#913](https://github.com/acquia/blt/pull/913) ([grasmash](https://github.com/grasmash))
- Updating BLT templated files. [\#912](https://github.com/acquia/blt/pull/912) ([grasmash](https://github.com/grasmash))
- Fixes \#857: Adding YAML linting target. [\#904](https://github.com/acquia/blt/pull/904) ([grasmash](https://github.com/grasmash))
- Add tagging of remote repo. [\#903](https://github.com/acquia/blt/pull/903) ([arknoll](https://github.com/arknoll))
- Fixes \#898: Upgrade to Drupal VM 4.1.0. [\#899](https://github.com/acquia/blt/pull/899) ([geerlingguy](https://github.com/geerlingguy))
- Reducing update output. [\#894](https://github.com/acquia/blt/pull/894) ([grasmash](https://github.com/grasmash))
- Adding update script to correct drupal scaffold excludes. [\#892](https://github.com/acquia/blt/pull/892) ([grasmash](https://github.com/grasmash))
- Fixes \#513: Updating Console to dev-master. [\#887](https://github.com/acquia/blt/pull/887) ([grasmash](https://github.com/grasmash))

**Fixed bugs:**

- project.yml modules.enable/disable empty sets overridden on BLT upgrade [\#916](https://github.com/acquia/blt/issues/916)
- Get "Query failed." during drush sql-sync in blt local:refresh [\#914](https://github.com/acquia/blt/issues/914)
- Schema updates get run on new installs [\#909](https://github.com/acquia/blt/issues/909)
- Drupal console exception [\#896](https://github.com/acquia/blt/issues/896)
- BLT Upgrades keep adding back modules to project.yml [\#888](https://github.com/acquia/blt/issues/888)
- Git commit messages are not being validated. [\#840](https://github.com/acquia/blt/issues/840)
- After Blt Update to 8.6.4 - array merge issue inside blt/project.yml file [\#821](https://github.com/acquia/blt/issues/821)
- Freshly-built project doesn't get a README.md file in project root [\#702](https://github.com/acquia/blt/issues/702)
- Fixes \#924: Remove nonexistent --db option from db-scrub script. [\#925](https://github.com/acquia/blt/pull/925) ([geerlingguy](https://github.com/geerlingguy))
- Ignore local.aliases.drush.php [\#910](https://github.com/acquia/blt/pull/910) ([timcosgrove](https://github.com/timcosgrove))
- Disabling deploy.tag prompt whenndeploy.branch is set. [\#908](https://github.com/acquia/blt/pull/908) ([grasmash](https://github.com/grasmash))
- Ignore local.services.yml [\#905](https://github.com/acquia/blt/pull/905) ([danepowell](https://github.com/danepowell))
- Fixes \#840: Git commit messages are not being validated. [\#901](https://github.com/acquia/blt/pull/901) ([shahinam](https://github.com/shahinam))
- Fixes \#893: Exclude patches of excluded packages. [\#895](https://github.com/acquia/blt/pull/895) ([grasmash](https://github.com/grasmash))
- Fixes \#888: BLT Upgrades keep adding back modules to project.yml. [\#891](https://github.com/acquia/blt/pull/891) ([grasmash](https://github.com/grasmash))

**Closed issues:**

- BLT custom settings include file [\#926](https://github.com/acquia/blt/issues/926)
- Cloud deployments fail during DB sanitization with 'Unknown option: --db' [\#924](https://github.com/acquia/blt/issues/924)
- blt \> vm:local:init - Error occurred while installing json \(2.0.2\) [\#917](https://github.com/acquia/blt/issues/917)
- Documentation: Onboarding docs for the whole BLT stack. [\#850](https://github.com/acquia/blt/issues/850)

**Misc merged pull requests**

- Update README.md [\#918](https://github.com/acquia/blt/pull/918) ([danepowell](https://github.com/danepowell))
- Documented setup:update task. [\#911](https://github.com/acquia/blt/pull/911) ([danepowell](https://github.com/danepowell))
- Containerize travis builds. [\#897](https://github.com/acquia/blt/pull/897) ([aweingarten](https://github.com/aweingarten))
- Fixes \#850: Adding documentation for entire toolset. [\#878](https://github.com/acquia/blt/pull/878) ([grasmash](https://github.com/grasmash))

## [8.6.6](https://github.com/acquia/blt/tree/8.6.6) (2016-12-28)
[Full Changelog](https://github.com/acquia/blt/compare/8.6.5...8.6.6)

**Implemented enhancements:**

- Modify composer blt-alias script so that it creates ~/.bash\_profile if it does not exist. [\#872](https://github.com/acquia/blt/issues/872)
- Remove default git.remotes url from project.yml on project create. [\#866](https://github.com/acquia/blt/issues/866)
- Improve Git commit hooks [\#825](https://github.com/acquia/blt/issues/825)
- Replace simplesaml gist [\#824](https://github.com/acquia/blt/issues/824)
- Re-introduce PhantomJS along side Selenium [\#787](https://github.com/acquia/blt/issues/787)
- Warn users using beta/alpha versions of modules [\#703](https://github.com/acquia/blt/issues/703)
- Add cloudhooks for scrubbing database [\#659](https://github.com/acquia/blt/issues/659)
- Automatically populate machine name for new projects [\#624](https://github.com/acquia/blt/issues/624)
- Automated testing for conflicting and overridden features [\#38](https://github.com/acquia/blt/issues/38)
- Bolt should allow deployment of tags not just branches [\#17](https://github.com/acquia/blt/issues/17)
- Fixes \#866: Remove default git.remotes url from project.yml on project create. [\#877](https://github.com/acquia/blt/pull/877) ([shahinam](https://github.com/shahinam))
- Fixes \#872: Modify composer blt-alias script so that it creates ~/.bash\_profile if it does not exist. [\#873](https://github.com/acquia/blt/pull/873) ([grasmash](https://github.com/grasmash))
- Fixes \#753: Add xdebug to Drupal VM by default. [\#868](https://github.com/acquia/blt/pull/868) ([grasmash](https://github.com/grasmash))
- Add php5.6-yaml package [\#848](https://github.com/acquia/blt/pull/848) ([bobbygryzynger](https://github.com/bobbygryzynger))
- Issue \#38: Test for features overrides. [\#841](https://github.com/acquia/blt/pull/841) ([danepowell](https://github.com/danepowell))
- Fixes \#787: Re-introduce PhantomJS along side Selenium. [\#839](https://github.com/acquia/blt/pull/839) ([grasmash](https://github.com/grasmash))
- Fixes \#824: Replace simplesaml gist. [\#838](https://github.com/acquia/blt/pull/838) ([grasmash](https://github.com/grasmash))
- Fixes \#624: Automatically populate machine name for new projects. [\#837](https://github.com/acquia/blt/pull/837) ([grasmash](https://github.com/grasmash))
- Fixes \#819: Making GitTest idempotent. [\#836](https://github.com/acquia/blt/pull/836) ([grasmash](https://github.com/grasmash))
- Initializing Drupal console during setup. [\#834](https://github.com/acquia/blt/pull/834) ([grasmash](https://github.com/grasmash))
- Adding @example tag to example Behat tests. [\#833](https://github.com/acquia/blt/pull/833) ([grasmash](https://github.com/grasmash))
- Fixes \#825: Adding color to commit msg error. [\#832](https://github.com/acquia/blt/pull/832) ([grasmash](https://github.com/grasmash))
- Removing field\_ui from default uninstall arrays. [\#826](https://github.com/acquia/blt/pull/826) ([grasmash](https://github.com/grasmash))
- Switching Pipelines to use MySQL service. [\#800](https://github.com/acquia/blt/pull/800) ([grasmash](https://github.com/grasmash))
- Downgrading to Drush 8. [\#728](https://github.com/acquia/blt/pull/728) ([grasmash](https://github.com/grasmash))
- Fixes \#659: Adding cloudhooks for basic database scrubbing. [\#697](https://github.com/acquia/blt/pull/697) ([grasmash](https://github.com/grasmash))

**Fixed bugs:**

- Drush commands error with "env: drush9: No such file or directory" [\#856](https://github.com/acquia/blt/issues/856)
- GitTasksTest::testGitPreCommitHook\(\) is destructive [\#819](https://github.com/acquia/blt/issues/819)
- Module schemas are not installed when enabling a module via config when running blt deploy [\#718](https://github.com/acquia/blt/issues/718)
- Fixes \#856: Drush commands error with "env: drush9: No such file or directory. [\#867](https://github.com/acquia/blt/pull/867) ([grasmash](https://github.com/grasmash))

**Closed issues:**

- Improve the Mac installation instructions so that it will be easier to onboard new users [\#865](https://github.com/acquia/blt/issues/865)
- Deployment workflow can be read like just for ACE [\#843](https://github.com/acquia/blt/issues/843)
- Add Pipelines documentation [\#829](https://github.com/acquia/blt/issues/829)
- Document that Acquia Connector should never have its configuration exported to code on Cloud [\#242](https://github.com/acquia/blt/issues/242)

**Misc merged pull requests**

- Fixes \#865: Improve the Mac installation instructions. [\#871](https://github.com/acquia/blt/pull/871) ([grasmash](https://github.com/grasmash))
- Improve instructions for Pipelines integration [\#869](https://github.com/acquia/blt/pull/869) ([kieranacquia](https://github.com/kieranacquia))
- More explicity replace default repo [\#864](https://github.com/acquia/blt/pull/864) ([kieranacquia](https://github.com/kieranacquia))
- Explain role of Pipelines [\#863](https://github.com/acquia/blt/pull/863) ([kieranacquia](https://github.com/kieranacquia))
- Update bash console with new blt command [\#860](https://github.com/acquia/blt/pull/860) ([kieranacquia](https://github.com/kieranacquia))
- Don't use undefined acronym "TA" in documentation [\#855](https://github.com/acquia/blt/pull/855) ([TravisCarden](https://github.com/TravisCarden))
- Update deploy.md [\#845](https://github.com/acquia/blt/pull/845) ([snize](https://github.com/snize))
- Fixing markdown spacing for readthedocs. [\#835](https://github.com/acquia/blt/pull/835) ([grasmash](https://github.com/grasmash))
- Adding Pipelines documentation. [\#828](https://github.com/acquia/blt/pull/828) ([grasmash](https://github.com/grasmash))
- Update RELEASE.md [\#763](https://github.com/acquia/blt/pull/763) ([grasmash](https://github.com/grasmash))

## [8.6.5](https://github.com/acquia/blt/tree/8.6.5) (2016-12-20)
[Full Changelog](https://github.com/acquia/blt/compare/8.6.4...8.6.5)

**Implemented enhancements:**

- Adding update hook to remove deprecated packages. [\#820](https://github.com/acquia/blt/pull/820) ([grasmash](https://github.com/grasmash))
- Adding grasmash/drupal-security-warning package dependency. [\#818](https://github.com/acquia/blt/pull/818) ([grasmash](https://github.com/grasmash))
- Ignoring @lightningextension tests in acquia-pipelines.yml. [\#816](https://github.com/acquia/blt/pull/816) ([grasmash](https://github.com/grasmash))
- Added Drush to DrupalVM. [\#815](https://github.com/acquia/blt/pull/815) ([danepowell](https://github.com/danepowell))
- Fixed memcache contamination. [\#814](https://github.com/acquia/blt/pull/814) ([danepowell](https://github.com/danepowell))

**Fixed bugs:**

- BLT not ignoring local Behat config file [\#822](https://github.com/acquia/blt/issues/822)
- Fixes \#822: Ignore Behat local.yml file. [\#823](https://github.com/acquia/blt/pull/823) ([geerlingguy](https://github.com/geerlingguy))

**Misc merged pull requests**

- Updating CHANGELOG.md for 8.6.3. [\#817](https://github.com/acquia/blt/pull/817) ([grasmash](https://github.com/grasmash))uia/blt/pull/814) ([danepowell](https://github.com/danepowell))

## [8.6.4](https://github.com/acquia/blt/tree/8.6.4) (2016-12-20)
[Full Changelog](https://github.com/acquia/blt/compare/8.6.3...8.6.4)

**Implemented enhancements:**

- Adding update hook to remove deprecated packages. [\#820](https://github.com/acquia/blt/pull/820) ([grasmash](https://github.com/grasmash))
- Adding grasmash/drupal-security-warning package dependency. [\#818](https://github.com/acquia/blt/pull/818) ([grasmash](https://github.com/grasmash))
- Ignoring @lightningextension tests in acquia-pipelines.yml. [\#816](https://github.com/acquia/blt/pull/816) ([grasmash](https://github.com/grasmash))
- Added Drush to DrupalVM. [\#815](https://github.com/acquia/blt/pull/815) ([danepowell](https://github.com/danepowell))

**Misc merged pull requests**

- Updating CHANGELOG.md for 8.6.3. [\#817](https://github.com/acquia/blt/pull/817) ([grasmash](https://github.com/grasmash))
- Fixed memcache contamination. [\#814](https://github.com/acquia/blt/pull/814) ([danepowell](https://github.com/danepowell))

## [8.6.3](https://github.com/acquia/blt/tree/8.6.3) (2016-12-19)
[Full Changelog](https://github.com/acquia/blt/compare/8.6.2...8.6.3)

**Implemented enhancements:**

- Change version constraint for drupal/core to ^8.0 [\#812](https://github.com/acquia/blt/pull/812) ([grasmash](https://github.com/grasmash))
- Fixes \#809: Add mailhog to Drupal VM installed\_extras. [\#810](https://github.com/acquia/blt/pull/810) ([geerlingguy](https://github.com/geerlingguy))

**Fixed bugs:**

- Hotfix for 8.6.2: drupal/core version constraint. [\#808](https://github.com/acquia/blt/pull/808) ([grasmash](https://github.com/grasmash))

**Closed issues:**

- Mailhog required for some commands [\#809](https://github.com/acquia/blt/issues/809)

## [8.6.2](https://github.com/acquia/blt/tree/8.6.2) (2016-12-19)
[Full Changelog](https://github.com/acquia/blt/compare/8.6.1...8.6.2)

**Implemented enhancements:**

- blt should have an acsf:init:drush command [\#779](https://github.com/acquia/blt/issues/779)
- blt acsf:init should be able to pull from dev repo [\#778](https://github.com/acquia/blt/issues/778)
- blt acsf:init should be able to increase its memory usage [\#777](https://github.com/acquia/blt/issues/777)
- Switch to use acquia/lightning and Drupal packagist [\#759](https://github.com/acquia/blt/issues/759)
- Ignoring new LightningExtension tests. [\#807](https://github.com/acquia/blt/pull/807) ([grasmash](https://github.com/grasmash))
- Reducing verbosity of memory\_limit expression. [\#801](https://github.com/acquia/blt/pull/801) ([grasmash](https://github.com/grasmash))
- Fixes \#796: Don't exclude local.\* files from build artifact. [\#797](https://github.com/acquia/blt/pull/797) ([geerlingguy](https://github.com/geerlingguy))
- Add defaults for deployment branch and message [\#793](https://github.com/acquia/blt/pull/793) ([bobbygryzynger](https://github.com/bobbygryzynger))
- Fixing bash conditionals. [\#792](https://github.com/acquia/blt/pull/792) ([grasmash](https://github.com/grasmash))
- Changing implementation of php.memory\_limit param. [\#791](https://github.com/acquia/blt/pull/791) ([grasmash](https://github.com/grasmash))
- Renaming acsf.repo\_branch to acsf.package. [\#789](https://github.com/acquia/blt/pull/789) ([grasmash](https://github.com/grasmash))
- Cleaning up output of setup tasks. [\#788](https://github.com/acquia/blt/pull/788) ([grasmash](https://github.com/grasmash))
- Reducing default verbosity of cloud hook tasks. [\#786](https://github.com/acquia/blt/pull/786) ([grasmash](https://github.com/grasmash))
- Fixes \#784: Update to Drupal VM 4.0.x. [\#785](https://github.com/acquia/blt/pull/785) ([geerlingguy](https://github.com/geerlingguy))
- Fixes \#779: Separate out composer from drush in acsf:init [\#782](https://github.com/acquia/blt/pull/782) ([nikgregory](https://github.com/nikgregory))
- Fixes \#778: Make it more flexible to change and acsf repository [\#781](https://github.com/acquia/blt/pull/781) ([nikgregory](https://github.com/nikgregory))
- issue-777 Make php memory limit settable [\#780](https://github.com/acquia/blt/pull/780) ([nikgregory](https://github.com/nikgregory))
- Updating template/composer.json to use Drupal packagist constraints. [\#762](https://github.com/acquia/blt/pull/762) ([grasmash](https://github.com/grasmash))
- Add multisite support during deploy and local setup tasks [\#736](https://github.com/acquia/blt/pull/736) ([bobbygryzynger](https://github.com/bobbygryzynger))
- Change private files path. [\#732](https://github.com/acquia/blt/pull/732) ([dpagini](https://github.com/dpagini))

**Fixed bugs:**

- Fixes \#772: Preventing trusted\_host.settings.php from being overwritten. [\#773](https://github.com/acquia/blt/pull/773) ([grasmash](https://github.com/grasmash))

**Closed issues:**

- Incorrect Cache Clear Option in Setup.xml [\#802](https://github.com/acquia/blt/issues/802)
- Don't exclude local.\* files from build artifact [\#796](https://github.com/acquia/blt/issues/796)
- Acquia Cloud Account Creation + CI Instructions [\#794](https://github.com/acquia/blt/issues/794)
- Update to Drupal VM 4.0.x [\#784](https://github.com/acquia/blt/issues/784)
- Acquia cloud workflow log and deploy:update in cloud hooks [\#783](https://github.com/acquia/blt/issues/783)
- BLT upgrade removed my customizations in trusted\_host.settings.php [\#772](https://github.com/acquia/blt/issues/772)

**Misc merged pull requests**

- Docs fix: Update path to project.yml. [\#804](https://github.com/acquia/blt/pull/804) ([geerlingguy](https://github.com/geerlingguy))
- BLT-802: replacing cc with cr [\#803](https://github.com/acquia/blt/pull/803) ([mikemadison13](https://github.com/mikemadison13))
- BLT-794: updating documentation to work around acquia cloud email san… [\#795](https://github.com/acquia/blt/pull/795) ([mikemadison13](https://github.com/mikemadison13))
- Adds gotchas to the INSTALL documentation [\#790](https://github.com/acquia/blt/pull/790) ([typhonius](https://github.com/typhonius))
- Fix 'ore' typo fix in Behat fail message. [\#776](https://github.com/acquia/blt/pull/776) ([geerlingguy](https://github.com/geerlingguy))
- Expanding documentation on adding BLT to existing projects. [\#774](https://github.com/acquia/blt/pull/774) ([grasmash](https://github.com/grasmash))

## [8.6.1](https://github.com/acquia/blt/tree/8.6.1) (2016-12-07)
[Full Changelog](https://github.com/acquia/blt/compare/8.6.0...8.6.1)

**Fixed bugs:**

- Removing testProd PHPUnit test. [\#771](https://github.com/acquia/blt/pull/771) ([grasmash](https://github.com/grasmash))
- Hotfix: strip special characters from version strings. [\#770](https://github.com/acquia/blt/pull/770) ([grasmash](https://github.com/grasmash))

## [8.6.0](https://github.com/acquia/blt/tree/8.6.0) (2016-12-07)
[Full Changelog](https://github.com/acquia/blt/compare/8.6.0-beta2...8.6.0)

**Implemented enhancements:**

- Removing search modules. These should be in separate feature project. [\#769](https://github.com/acquia/blt/pull/769) ([grasmash](https://github.com/grasmash))
- Making CI builds more verbose. [\#767](https://github.com/acquia/blt/pull/767) ([grasmash](https://github.com/grasmash))
- Updating acquia-pipelines.yml to create db only if not exists. [\#766](https://github.com/acquia/blt/pull/766) ([grasmash](https://github.com/grasmash))

**Fixed bugs:**

- Fixing bug that prevents modules from being toggled. [\#768](https://github.com/acquia/blt/pull/768) ([grasmash](https://github.com/grasmash))
- Preserving development.services.yml. [\#758](https://github.com/acquia/blt/pull/758) ([grasmash](https://github.com/grasmash))

## [8.6.0-beta2](https://github.com/acquia/blt/tree/8.6.0-beta2) (2016-12-06)
[Full Changelog](https://github.com/acquia/blt/compare/8.6.0-beta1...8.6.0-beta2)

**Implemented enhancements:**

- Improve upgrade path for older versions of BLT [\#704](https://github.com/acquia/blt/issues/704)
- Validate patched package version constraints [\#685](https://github.com/acquia/blt/issues/685)
- More granular control of modules per environment [\#668](https://github.com/acquia/blt/issues/668)
- Moving drush to require dependencies interferes with Acquia-provided drush commands on ACE [\#647](https://github.com/acquia/blt/issues/647)
- Displaying update output. [\#755](https://github.com/acquia/blt/pull/755) ([grasmash](https://github.com/grasmash))
- Printing message when blt update fails. [\#754](https://github.com/acquia/blt/pull/754) ([grasmash](https://github.com/grasmash))
- Updating .travis.yml dynamically. [\#752](https://github.com/acquia/blt/pull/752) ([grasmash](https://github.com/grasmash))
- Comparing checksum of composer.json before and after blt update. [\#748](https://github.com/acquia/blt/pull/748) ([grasmash](https://github.com/grasmash))
- Disabling Lightning Behat tests on pull requests to BLT. [\#747](https://github.com/acquia/blt/pull/747) ([grasmash](https://github.com/grasmash))
- Fixes \#716: Removing cruft from deployment artifact. [\#746](https://github.com/acquia/blt/pull/746) ([grasmash](https://github.com/grasmash))
- Speeding up composer validate in pre-commit hook. [\#745](https://github.com/acquia/blt/pull/745) ([grasmash](https://github.com/grasmash))
- Preventing overwrite of custom development.services.yml. [\#744](https://github.com/acquia/blt/pull/744) ([grasmash](https://github.com/grasmash))
- Making requirement for composer.lock update after BLT update more clear. [\#741](https://github.com/acquia/blt/pull/741) ([grasmash](https://github.com/grasmash))
- Fixes \#704: Improve upgrade path for older versions of BLT. [\#740](https://github.com/acquia/blt/pull/740) ([grasmash](https://github.com/grasmash))
- Ignoring PHPStorm files. [\#734](https://github.com/acquia/blt/pull/734) ([grasmash](https://github.com/grasmash))
- Fixed munged YAML indentation. [\#731](https://github.com/acquia/blt/pull/731) ([danepowell](https://github.com/danepowell))
- Upgrading cweagens/composer-patches to 1.6.0. [\#730](https://github.com/acquia/blt/pull/730) ([grasmash](https://github.com/grasmash))
- Generating default lightning.extend.yml on project creation. [\#726](https://github.com/acquia/blt/pull/726) ([grasmash](https://github.com/grasmash))
- Fixes \#712: Fix BLT's ReadTheDocs search. [\#713](https://github.com/acquia/blt/pull/713) ([geerlingguy](https://github.com/geerlingguy))
- Exit gracefully when `blt` command cannot be run [\#709](https://github.com/acquia/blt/pull/709) ([TravisCarden](https://github.com/TravisCarden))
- Fixes \#647: Include Acquia cloud drush commands with BLT provided drush. [\#696](https://github.com/acquia/blt/pull/696) ([grasmash](https://github.com/grasmash))

**Fixed bugs:**

- Twig Linting doesn't recognize Drupal functions/filters/etc [\#737](https://github.com/acquia/blt/issues/737)
- Travis CI Drupal site install failing \(as of today\) [\#720](https://github.com/acquia/blt/issues/720)
- Travis builds leave cruft in the deploy artifact [\#716](https://github.com/acquia/blt/issues/716)
- post-code-update cloud hook fails to enable non-existant Shield module using default composer.json and project.yml [\#710](https://github.com/acquia/blt/issues/710)
- blt local:refresh fails after upgrade to 8.6.0-beta1 [\#699](https://github.com/acquia/blt/issues/699)
- Disable default cloud hooks on ACSF [\#664](https://github.com/acquia/blt/issues/664)
- Fixing bug in travis.yml syntax. [\#751](https://github.com/acquia/blt/pull/751) ([grasmash](https://github.com/grasmash))
- Fixes \#737: Twig validation does not recognize Drupal filters/functions. [\#743](https://github.com/acquia/blt/pull/743) ([grasmash](https://github.com/grasmash))
- Fixed composer errors on ACE. [\#735](https://github.com/acquia/blt/pull/735) ([danepowell](https://github.com/danepowell))
- Fixed errors on ACE deploys. [\#733](https://github.com/acquia/blt/pull/733) ([danepowell](https://github.com/danepowell))
- Fixes \#710: Adding shield module to composer.json template. [\#729](https://github.com/acquia/blt/pull/729) ([grasmash](https://github.com/grasmash))
- Issue \#704: Fixed update hooks. [\#724](https://github.com/acquia/blt/pull/724) ([danepowell](https://github.com/danepowell))
- Issue \#719: Fixed failing setup:git-hooks. [\#723](https://github.com/acquia/blt/pull/723) ([danepowell](https://github.com/danepowell))
- Fixes \#720: Fixing MySQL connection issue caused by use of localhost. [\#722](https://github.com/acquia/blt/pull/722) ([grasmash](https://github.com/grasmash))
- Fixed cloud hooks. [\#698](https://github.com/acquia/blt/pull/698) ([danepowell](https://github.com/danepowell))
- Fixes \#664: Disable cloud hooks on ACSF. [\#695](https://github.com/acquia/blt/pull/695) ([grasmash](https://github.com/grasmash))

**Closed issues:**

- Examples link is broken in documentation [\#742](https://github.com/acquia/blt/issues/742)
- BLT setup:git-hooks fails [\#719](https://github.com/acquia/blt/issues/719)
- Fix BLT's ReadTheDocs Search [\#712](https://github.com/acquia/blt/issues/712)
- Acquia Cloud Hooks not executable by default [\#711](https://github.com/acquia/blt/issues/711)
- Exit gracefully if `blt` command can't be run [\#708](https://github.com/acquia/blt/issues/708)

**Misc merged pull requests**

- Referencing release notes in update instructions. [\#756](https://github.com/acquia/blt/pull/756) ([grasmash](https://github.com/grasmash))
- Updating brew docs. [\#725](https://github.com/acquia/blt/pull/725) ([grasmash](https://github.com/grasmash))
- Clarifying install instructions. [\#717](https://github.com/acquia/blt/pull/717) ([grasmash](https://github.com/grasmash))
- Adding clarity to documentation. [\#707](https://github.com/acquia/blt/pull/707) ([grasmash](https://github.com/grasmash))
- Toggle modules per environment. [\#700](https://github.com/acquia/blt/pull/700) ([danepowell](https://github.com/danepowell))
- Fix and expand SimpleSAML docs further [\#694](https://github.com/acquia/blt/pull/694) ([TravisCarden](https://github.com/TravisCarden))

## [8.6.0-beta1](https://github.com/acquia/blt/tree/8.6.0-beta1) (2016-11-21)
[Full Changelog](https://github.com/acquia/blt/compare/8.5.2...8.6.0-beta1)

**Implemented enhancements:**

- Git-hooks configuration [\#628](https://github.com/acquia/blt/issues/628)
- Clean up repo root--move top-level BLT files into their own directory [\#604](https://github.com/acquia/blt/issues/604)
- Implement a hook\_update\_n\(\) analog  [\#600](https://github.com/acquia/blt/issues/600)
- Add Twig syntax check to Git pre-commit hook [\#44](https://github.com/acquia/blt/issues/44)
- Fixes \#628: Allowing custom git hooks to be used. [\#692](https://github.com/acquia/blt/pull/692) ([grasmash](https://github.com/grasmash))
- Preventing PHPUnit failure when project.local.yml is empty. [\#691](https://github.com/acquia/blt/pull/691) ([grasmash](https://github.com/grasmash))
- Making target hooks more verbose. [\#687](https://github.com/acquia/blt/pull/687) ([grasmash](https://github.com/grasmash))
- Removed cloud hook samples. [\#684](https://github.com/acquia/blt/pull/684) ([danepowell](https://github.com/danepowell))
- Fixes \#604: Moving root blt files to blt subdir. [\#676](https://github.com/acquia/blt/pull/676) ([grasmash](https://github.com/grasmash))
- Validating composer.json in pre-commit hook. [\#672](https://github.com/acquia/blt/pull/672) ([grasmash](https://github.com/grasmash))
- Adding validate:twig target. [\#665](https://github.com/acquia/blt/pull/665) ([grasmash](https://github.com/grasmash))
- Add .theme files to validation patternset [\#660](https://github.com/acquia/blt/pull/660) ([bobbygryzynger](https://github.com/bobbygryzynger))

**Fixed bugs:**

- Git Hooks Fail after BLT Update [\#679](https://github.com/acquia/blt/issues/679)
- Travis and D8 CMI [\#657](https://github.com/acquia/blt/issues/657)
- Ensuring that drush policy applies only to AC. [\#693](https://github.com/acquia/blt/pull/693) ([grasmash](https://github.com/grasmash))
- Updating schema version after updates. [\#686](https://github.com/acquia/blt/pull/686) ([grasmash](https://github.com/grasmash))
- Fixing default acquia-pipelines.yml for new project.yml location. [\#683](https://github.com/acquia/blt/pull/683) ([grasmash](https://github.com/grasmash))
- Resolves \#679: Twig validation hook fails. [\#680](https://github.com/acquia/blt/pull/680) ([grasmash](https://github.com/grasmash))
- Don't uninstall dblog locally. [\#675](https://github.com/acquia/blt/pull/675) ([bobbygryzynger](https://github.com/bobbygryzynger))
- Resolves \#670: Remove duplicate config imports. [\#674](https://github.com/acquia/blt/pull/674) ([grasmash](https://github.com/grasmash))
- Made cloud hooks executable. [\#648](https://github.com/acquia/blt/pull/648) ([danepowell](https://github.com/danepowell))

**Closed issues:**

- How to handle D8 config with local/deploy modules [\#670](https://github.com/acquia/blt/issues/670)
- Is adding BLT to an existing project still a viable option? [\#650](https://github.com/acquia/blt/issues/650)

**Misc merged pull requests**

- Adding docs for configuration overrides. [\#690](https://github.com/acquia/blt/pull/690) ([grasmash](https://github.com/grasmash))
- Adding Pull Request template. [\#677](https://github.com/acquia/blt/pull/677) ([grasmash](https://github.com/grasmash))
- Fix SimpleSaml Markdown formatting. [\#673](https://github.com/acquia/blt/pull/673) ([grasmash](https://github.com/grasmash))
- Validated SimpleSAML docs Markdown fix [\#669](https://github.com/acquia/blt/pull/669) ([TravisCarden](https://github.com/TravisCarden))
- ReadTheDocs is not good at Markdown [\#667](https://github.com/acquia/blt/pull/667) ([TravisCarden](https://github.com/TravisCarden))
- Correct SimpleSAML documentation [\#666](https://github.com/acquia/blt/pull/666) ([TravisCarden](https://github.com/TravisCarden))
- Fix 'blt version' [\#656](https://github.com/acquia/blt/pull/656) ([TravisCarden](https://github.com/TravisCarden))
- Update Drupal VM to latest stable release. [\#655](https://github.com/acquia/blt/pull/655) ([geerlingguy](https://github.com/geerlingguy))
- Rebuild caches before running updates. [\#652](https://github.com/acquia/blt/pull/652) ([danepowell](https://github.com/danepowell))
- Adding updater for specific BLT delta migrations. [\#601](https://github.com/acquia/blt/pull/601) ([grasmash](https://github.com/grasmash))

## [8.5.2](https://github.com/acquia/blt/tree/8.5.2) (2016-11-09)
[Full Changelog](https://github.com/acquia/blt/compare/8.5.1...8.5.2)

**Implemented enhancements:**

- Allow cm.features.bundle to contain multiple feature bundles [\#626](https://github.com/acquia/blt/issues/626)
- Add patch for .htaccess for SSL Only on Acquia Cloud [\#598](https://github.com/acquia/blt/issues/598)
- Add blt target for use in cloud-hooks [\#594](https://github.com/acquia/blt/issues/594)
- Fixing toggle-modules targets. [\#644](https://github.com/acquia/blt/pull/644) ([grasmash](https://github.com/grasmash))
- Adding local:toggle-modules and deploy:toggle-modules targets. [\#643](https://github.com/acquia/blt/pull/643) ([grasmash](https://github.com/grasmash))
- Added trusted host configuration. [\#640](https://github.com/acquia/blt/pull/640) ([danepowell](https://github.com/danepowell))
- Removing uncustomized scaffold files [\#632](https://github.com/acquia/blt/pull/632) ([grasmash](https://github.com/grasmash))
- Requiring Lightning ^8.1.12. [\#631](https://github.com/acquia/blt/pull/631) ([grasmash](https://github.com/grasmash))
- Allow specification of multiple Features bundles [\#627](https://github.com/acquia/blt/pull/627) ([timcosgrove](https://github.com/timcosgrove))
- Removed features patch. [\#625](https://github.com/acquia/blt/pull/625) ([danepowell](https://github.com/danepowell))
- Run BLT commands on ACE [\#589](https://github.com/acquia/blt/pull/589) ([danepowell](https://github.com/danepowell))

**Fixed bugs:**

- development.services.yml gets clobbered on updates [\#641](https://github.com/acquia/blt/issues/641)
- Features patch no longer applies [\#630](https://github.com/acquia/blt/issues/630)
- Build failing [\#622](https://github.com/acquia/blt/issues/622)
- Fixes \#641: Preventing overwrite of customized scaffold files. [\#642](https://github.com/acquia/blt/pull/642) ([grasmash](https://github.com/grasmash))
- Ignoring experimental tests on Pipelines builds. [\#620](https://github.com/acquia/blt/pull/620) ([grasmash](https://github.com/grasmash))

**Closed issues:**

- Running blt local:setup fails [\#635](https://github.com/acquia/blt/issues/635)
- Features patch error [\#634](https://github.com/acquia/blt/issues/634)
- Fails with Permission denied on blt.sh [\#616](https://github.com/acquia/blt/issues/616)

**Misc merged pull requests**

- Add profile options documentation [\#637](https://github.com/acquia/blt/pull/637) ([bobbygryzynger](https://github.com/bobbygryzynger))
- Fix docs url [\#633](https://github.com/acquia/blt/pull/633) ([bobbygryzynger](https://github.com/bobbygryzynger))
- Added and refactored some of the information for SimpleSAMLPHP setup. [\#629](https://github.com/acquia/blt/pull/629) ([wouteradem](https://github.com/wouteradem))
- Add instructions for Fedora [\#623](https://github.com/acquia/blt/pull/623) ([anavarre](https://github.com/anavarre))
- Expand and improve SimpleSAMLphp documentation [\#621](https://github.com/acquia/blt/pull/621) ([TravisCarden](https://github.com/TravisCarden))

## [8.5.1](https://github.com/acquia/blt/tree/8.5.1) (2016-11-01)
[Full Changelog](https://github.com/acquia/blt/compare/8.5.0...8.5.1)

**Implemented enhancements:**

- Enabling Lightning tests by default on new projects. [\#618](https://github.com/acquia/blt/pull/618) ([grasmash](https://github.com/grasmash))
- Improving output of setup tasks. [\#617](https://github.com/acquia/blt/pull/617) ([grasmash](https://github.com/grasmash))
- Revert "Automated SimpleSAMLphp htaccess patch." [\#605](https://github.com/acquia/blt/pull/605) ([grasmash](https://github.com/grasmash))
- Automated SimpleSAMLphp htaccess patch. [\#603](https://github.com/acquia/blt/pull/603) ([danepowell](https://github.com/danepowell))
- Added sample patches for SSL and SAML. [\#602](https://github.com/acquia/blt/pull/602) ([danepowell](https://github.com/danepowell))
- Adding composer/installers. [\#592](https://github.com/acquia/blt/pull/592) ([grasmash](https://github.com/grasmash))
- Removed duplicate dependencies. [\#591](https://github.com/acquia/blt/pull/591) ([danepowell](https://github.com/danepowell))

**Fixed bugs:**

- Disabling chromedriver installation on Pipelines. [\#619](https://github.com/acquia/blt/pull/619) ([grasmash](https://github.com/grasmash))
- Fixed installation of packages with commit refs. [\#606](https://github.com/acquia/blt/pull/606) ([danepowell](https://github.com/danepowell))
- Adding include path to drush.wrapper. [\#593](https://github.com/acquia/blt/pull/593) ([grasmash](https://github.com/grasmash))

**Closed issues:**

- Broken link to next-steps.md from creating-new-project.md [\#612](https://github.com/acquia/blt/issues/612)
- Issues with default install process  [\#567](https://github.com/acquia/blt/issues/567)
- Behat tests fail on Dev Desktop [\#561](https://github.com/acquia/blt/issues/561)
- Doctor fails on new install in Dev Desktop [\#560](https://github.com/acquia/blt/issues/560)
- Doctor doesn't recognize Dev Desktop sites [\#558](https://github.com/acquia/blt/issues/558)
- Error after deploying artifact branch build with blt deploy.  [\#533](https://github.com/acquia/blt/issues/533)
- Behat: First test tagged @javascript in a feature fails, subsequent pass [\#456](https://github.com/acquia/blt/issues/456)

**Misc merged pull requests**

- Updating docs, adding video links and next steps. [\#614](https://github.com/acquia/blt/pull/614) ([grasmash](https://github.com/grasmash))
- Fixed broken link to git-hooks documentation. [\#611](https://github.com/acquia/blt/pull/611) ([larruda](https://github.com/larruda))
- Fix Drupal-VM config name drupal\_db\_name [\#597](https://github.com/acquia/blt/pull/597) ([dpagini](https://github.com/dpagini))
- Improvements to the Ubuntu Bash on Windows documentation. [\#596](https://github.com/acquia/blt/pull/596) ([srowlands](https://github.com/srowlands))

## [8.5.0](https://github.com/acquia/blt/tree/8.5.0) (2016-10-19)
[Full Changelog](https://github.com/acquia/blt/compare/8.5.0-beta1...8.5.0)

**Implemented enhancements:**

- Toning down verbosity a bit. [\#588](https://github.com/acquia/blt/pull/588) ([grasmash](https://github.com/grasmash))
- Defining composer version contraints using carets. [\#587](https://github.com/acquia/blt/pull/587) ([grasmash](https://github.com/grasmash))
- Temporarily pinning to Lightning 8.1.x-dev. [\#586](https://github.com/acquia/blt/pull/586) ([grasmash](https://github.com/grasmash))
- Fixing user directory. [\#585](https://github.com/acquia/blt/pull/585) ([grasmash](https://github.com/grasmash))
- Updating post-provision.sh for Drupal VM. [\#584](https://github.com/acquia/blt/pull/584) ([grasmash](https://github.com/grasmash))
- Considering "default" as an unset URI in doctor. [\#582](https://github.com/acquia/blt/pull/582) ([grasmash](https://github.com/grasmash))
- Reducing DrushTask default verbosity. [\#581](https://github.com/acquia/blt/pull/581) ([grasmash](https://github.com/grasmash))
- Making doctor work with DD/VM/MAMP. [\#580](https://github.com/acquia/blt/pull/580) ([grasmash](https://github.com/grasmash))
- Tweak simplesaml config. [\#579](https://github.com/acquia/blt/pull/579) ([danepowell](https://github.com/danepowell))
- Adding more verbose Behat output. [\#577](https://github.com/acquia/blt/pull/577) ([grasmash](https://github.com/grasmash))
- Fixing composer excludes. [\#576](https://github.com/acquia/blt/pull/576) ([grasmash](https://github.com/grasmash))
- Improving composer integration UX. [\#574](https://github.com/acquia/blt/pull/574) ([grasmash](https://github.com/grasmash))
- Fixes \#557: Allow packages to be excluded from BLT templated updates. [\#572](https://github.com/acquia/blt/pull/572) ([grasmash](https://github.com/grasmash))
- Prompting for re-generation of behat local.yml. [\#571](https://github.com/acquia/blt/pull/571) ([grasmash](https://github.com/grasmash))
- Ignoring @preview tag in Behat tests \(experimental\). [\#570](https://github.com/acquia/blt/pull/570) ([grasmash](https://github.com/grasmash))
- Executing lightning tests only on 8.x. [\#569](https://github.com/acquia/blt/pull/569) ([grasmash](https://github.com/grasmash))
- Default local trusted host settings [\#566](https://github.com/acquia/blt/pull/566) ([jrbeeman](https://github.com/jrbeeman))
- Change default local settings for visibility of test projects and rebuild access [\#565](https://github.com/acquia/blt/pull/565) ([jrbeeman](https://github.com/jrbeeman))
- Removing Lightning patch. [\#564](https://github.com/acquia/blt/pull/564) ([grasmash](https://github.com/grasmash))
- Fixes \#562. Add a version target to the blt phing file to display version info. [\#563](https://github.com/acquia/blt/pull/563) ([gollyg](https://github.com/gollyg))
- Disabling Selenium tests for Pipelines. [\#556](https://github.com/acquia/blt/pull/556) ([grasmash](https://github.com/grasmash))

**Fixed bugs:**

- Updating composer-patches to latest dev-master commit. [\#590](https://github.com/acquia/blt/pull/590) ([grasmash](https://github.com/grasmash))
- Fixing doctor when used with symlinks. [\#575](https://github.com/acquia/blt/pull/575) ([grasmash](https://github.com/grasmash))
- Cleaned up SimpleSAML\_php integration. [\#555](https://github.com/acquia/blt/pull/555) ([danepowell](https://github.com/danepowell))

**Closed issues:**

- Drupal VM integration broken in latest stable release of DrupalVM [\#568](https://github.com/acquia/blt/issues/568)
- Add BLT command to show current version [\#562](https://github.com/acquia/blt/issues/562)
- Doctor doesn't recognize DEVDESKTOP\_DRUPAL\_SETTINGS\_DIR on Dev Desktop [\#559](https://github.com/acquia/blt/issues/559)
- Allow packages to be excluded from BLT templated updates [\#557](https://github.com/acquia/blt/issues/557)

**Misc merged pull requests**

- Add documentation for installing the chromedriver in OSX. [\#578](https://github.com/acquia/blt/pull/578) ([gollyg](https://github.com/gollyg))

## [8.5.0-beta1](https://github.com/acquia/blt/tree/8.5.0-beta1) (2016-10-13)
[Full Changelog](https://github.com/acquia/blt/compare/8.4.9...8.5.0-beta1)

**Implemented enhancements:**

- Add verbosity control variable to BLT. Reduce default verbosity. [\#529](https://github.com/acquia/blt/issues/529)
- Update Documentation for BLT under WSL [\#509](https://github.com/acquia/blt/issues/509)
- Add support for SimpleSAMLphp [\#481](https://github.com/acquia/blt/issues/481)
- Harmonize ACE and ACSF deploy artifacts [\#164](https://github.com/acquia/blt/issues/164)
- Hiding drush status check output in doctor command. [\#554](https://github.com/acquia/blt/pull/554) ([grasmash](https://github.com/grasmash))
- Adding blt alias to Drupal VM. [\#552](https://github.com/acquia/blt/pull/552) ([grasmash](https://github.com/grasmash))
- Modifying template .travis.yml. [\#550](https://github.com/acquia/blt/pull/550) ([grasmash](https://github.com/grasmash))
- Moving doctor output to table. [\#548](https://github.com/acquia/blt/pull/548) ([grasmash](https://github.com/grasmash))
- Check date.timezone in doctor. [\#547](https://github.com/acquia/blt/pull/547) ([srowlands](https://github.com/srowlands))
- Deploying on only one PHP build. [\#545](https://github.com/acquia/blt/pull/545) ([grasmash](https://github.com/grasmash))
- Upping Phing's memory limit to 1G. [\#541](https://github.com/acquia/blt/pull/541) ([grasmash](https://github.com/grasmash))
- Added adminer to DrupalVM. [\#538](https://github.com/acquia/blt/pull/538) ([danepowell](https://github.com/danepowell))
- Change drush.wrapper to not run 'composer install' automatically [\#535](https://github.com/acquia/blt/pull/535) ([TravisCarden](https://github.com/TravisCarden))
- Adding more verbosity control variables. [\#530](https://github.com/acquia/blt/pull/530) ([grasmash](https://github.com/grasmash))
- Adding tests/phpunit/Bolt to cleanup. [\#528](https://github.com/acquia/blt/pull/528) ([grasmash](https://github.com/grasmash))
- Only deploy on a single php version. [\#524](https://github.com/acquia/blt/pull/524) ([srowlands](https://github.com/srowlands))
- Ensure correct exit status on pre-commit [\#523](https://github.com/acquia/blt/pull/523) ([steveworley](https://github.com/steveworley))
- Switching PhantomJS to Selenium [\#520](https://github.com/acquia/blt/pull/520) ([grasmash](https://github.com/grasmash))
- Issue \#509: Use default hostname that works more universally. [\#516](https://github.com/acquia/blt/pull/516) ([geerlingguy](https://github.com/geerlingguy))
- Issue \#509: Adjust drush wrapper bin path so it works on Windows with bad symlinks. [\#515](https://github.com/acquia/blt/pull/515) ([geerlingguy](https://github.com/geerlingguy))
- Follow-up to \#502: Remove bz2 requirement after switch to Selenium2. [\#503](https://github.com/acquia/blt/pull/503) ([geerlingguy](https://github.com/geerlingguy))

**Fixed bugs:**

- Detect environment variables correctly with doctor. [\#546](https://github.com/acquia/blt/pull/546) ([srowlands](https://github.com/srowlands))
- Fixing Drupal VM integration with Selenium. [\#544](https://github.com/acquia/blt/pull/544) ([grasmash](https://github.com/grasmash))
- Fixing coder syntax violation. [\#540](https://github.com/acquia/blt/pull/540) ([grasmash](https://github.com/grasmash))
- Fixes \#514: Accomodating spaces in dir path. [\#532](https://github.com/acquia/blt/pull/532) ([grasmash](https://github.com/grasmash))
- Fixes \#506: Include config in deploy artifact. [\#511](https://github.com/acquia/blt/pull/511) ([dooleymatt](https://github.com/dooleymatt))

**Closed issues:**

- \[Feature Request\] Provide the option to manage the jquery libraries [\#525](https://github.com/acquia/blt/issues/525)
- DrupalVM init fails due to missing package php-bz2 [\#521](https://github.com/acquia/blt/issues/521)
- Space in project directory path causes initial composer create-project to fail.  [\#514](https://github.com/acquia/blt/issues/514)
- SimpleSAMLphp config files not included in deploy artifact [\#506](https://github.com/acquia/blt/issues/506)
- Document a typical update process, including BLT and a major dependency [\#493](https://github.com/acquia/blt/issues/493)
- Confusion over setting up with Acquia DevDesktop [\#369](https://github.com/acquia/blt/issues/369)

**Misc merged pull requests**

- Update to Drupal VM 3.4.x with Selenium and Chromedriver. [\#549](https://github.com/acquia/blt/pull/549) ([geerlingguy](https://github.com/geerlingguy))
- Include Instructions for VM configuration / customization on Create New Projects Page [\#542](https://github.com/acquia/blt/pull/542) ([mikemadison13](https://github.com/mikemadison13))
- Correcting typos in BLT Drush template and Onboarding documentation [\#539](https://github.com/acquia/blt/pull/539) ([mikemadison13](https://github.com/mikemadison13))
- Fixes \#369: Documenting DD php bin .bash\_profile example. [\#537](https://github.com/acquia/blt/pull/537) ([grasmash](https://github.com/grasmash))
- Fix typo that was breaking the dependency management link. [\#534](https://github.com/acquia/blt/pull/534) ([jrbeeman](https://github.com/jrbeeman))
- Adding docs for front end dependencies. [\#527](https://github.com/acquia/blt/pull/527) ([grasmash](https://github.com/grasmash))
- Document that users may need to run composer update after updating BLT. [\#522](https://github.com/acquia/blt/pull/522) ([jrbeeman](https://github.com/jrbeeman))
- Adding next steps docs. [\#518](https://github.com/acquia/blt/pull/518) ([grasmash](https://github.com/grasmash))

## [8.4.9](https://github.com/acquia/blt/tree/8.4.9) (2016-10-07)
[Full Changelog](https://github.com/acquia/blt/compare/8.4.8...8.4.9)

**Implemented enhancements:**

- Fix typo in VirtualBox missing error message. [\#508](https://github.com/acquia/blt/pull/508) ([geerlingguy](https://github.com/geerlingguy))
- Fixes \#504: Use newer version of Drupal VM, 3.3.x. [\#507](https://github.com/acquia/blt/pull/507) ([geerlingguy](https://github.com/geerlingguy))
- Fixes \#501: Default Drupal VM to PHP 5.6. [\#502](https://github.com/acquia/blt/pull/502) ([geerlingguy](https://github.com/geerlingguy))
- Ensuring that a hash salt is generated prior to deployment. [\#497](https://github.com/acquia/blt/pull/497) ([grasmash](https://github.com/grasmash))
- BLT-481: BLT/SimpleSAMLphp Integration [\#478](https://github.com/acquia/blt/pull/478) ([dooleymatt](https://github.com/dooleymatt))

**Fixed bugs:**

- Fixing broken drush test. [\#512](https://github.com/acquia/blt/pull/512) ([grasmash](https://github.com/grasmash))

**Closed issues:**

- Use newer version of Drupal VM \(currently ~3.1\) [\#504](https://github.com/acquia/blt/issues/504)
- Default DrupalVM to PHP 5.6 [\#501](https://github.com/acquia/blt/issues/501)

**Misc merged pull requests**

- Issue \#509: Update Documentation for BLT under WSL. [\#510](https://github.com/acquia/blt/pull/510) ([geerlingguy](https://github.com/geerlingguy))

## [8.4.8](https://github.com/acquia/blt/tree/8.4.8) (2016-10-05)
[Full Changelog](https://github.com/acquia/blt/compare/8.4.7...8.4.8)

**Implemented enhancements:**

- Clarifying doctor output for Behat config issues. [\#494](https://github.com/acquia/blt/pull/494) ([grasmash](https://github.com/grasmash))

**Fixed bugs:**

- Fixing acsf:init bug preventing include from being written. [\#496](https://github.com/acquia/blt/pull/496) ([grasmash](https://github.com/grasmash))
- Removing Lightning target hooks. [\#495](https://github.com/acquia/blt/pull/495) ([grasmash](https://github.com/grasmash))

## [8.4.7](https://github.com/acquia/blt/tree/8.4.7) (2016-10-05)
[Full Changelog](https://github.com/acquia/blt/compare/8.4.6...8.4.7)

**Implemented enhancements:**

- Disable twig cache for local development [\#477](https://github.com/acquia/blt/issues/477)
- Adding composer nuke script. [\#491](https://github.com/acquia/blt/pull/491) ([grasmash](https://github.com/grasmash))
- Updating spacing in composer.json. [\#490](https://github.com/acquia/blt/pull/490) ([grasmash](https://github.com/grasmash))
- Removing setup:behat from setup:settings. [\#488](https://github.com/acquia/blt/pull/488) ([grasmash](https://github.com/grasmash))
- Adding drush policy file to prevent drush9 usage on Acquia Cloud. [\#487](https://github.com/acquia/blt/pull/487) ([grasmash](https://github.com/grasmash))
- Resolves \#477: Disable twig cache for local development. [\#482](https://github.com/acquia/blt/pull/482) ([grasmash](https://github.com/grasmash))
- Consistent settings.php permissions. [\#480](https://github.com/acquia/blt/pull/480) ([danepowell](https://github.com/danepowell))
- Added features bundle argument. [\#479](https://github.com/acquia/blt/pull/479) ([danepowell](https://github.com/danepowell))
- Removing hosting flag for acsf. [\#471](https://github.com/acquia/blt/pull/471) ([grasmash](https://github.com/grasmash))
- Deploy build tweak. [\#467](https://github.com/acquia/blt/pull/467) ([danepowell](https://github.com/danepowell))
- Allowing single Behat scenario to be executed via BLT. [\#466](https://github.com/acquia/blt/pull/466) ([grasmash](https://github.com/grasmash))

**Misc merged pull requests**

- Updated deployment code and documentation for travis-ci. [\#492](https://github.com/acquia/blt/pull/492) ([aweingarten](https://github.com/aweingarten))
- Update ci.md [\#485](https://github.com/acquia/blt/pull/485) ([danepowell](https://github.com/danepowell))
- Fix missing links and formatting in Windows setup docs. [\#476](https://github.com/acquia/blt/pull/476) ([srowlands](https://github.com/srowlands))
- Add documentation for using BLT with Ubuntu Bash on Windows 10. [\#475](https://github.com/acquia/blt/pull/475) ([srowlands](https://github.com/srowlands))
- Cleaned up live testing docs. [\#473](https://github.com/acquia/blt/pull/473) ([danepowell](https://github.com/danepowell))
- Simplifying install instructions. [\#472](https://github.com/acquia/blt/pull/472) ([grasmash](https://github.com/grasmash))

## [8.4.6](https://github.com/acquia/blt/tree/8.4.6) (2016-09-27)
[Full Changelog](https://github.com/acquia/blt/compare/8.4.5...8.4.6)

**Implemented enhancements:**

- Improved DX with DrupalVM [\#441](https://github.com/acquia/blt/issues/441)
- Remote Drush commands from BLT repo root [\#426](https://github.com/acquia/blt/issues/426)
- Allow targets to be disabled in project.yml [\#290](https://github.com/acquia/blt/issues/290)
- Improving Behat + Drupal VM integration. [\#462](https://github.com/acquia/blt/pull/462) ([grasmash](https://github.com/grasmash))
- Adding cog as a default dependency. [\#460](https://github.com/acquia/blt/pull/460) ([grasmash](https://github.com/grasmash))
- Hiding more targets from list. [\#459](https://github.com/acquia/blt/pull/459) ([grasmash](https://github.com/grasmash))
- Closes \#290: Allow targets to be disabled in project.yml. [\#458](https://github.com/acquia/blt/pull/458) ([grasmash](https://github.com/grasmash))
- Resolves \#441: Improving DX of DrupalVM integration [\#457](https://github.com/acquia/blt/pull/457) ([grasmash](https://github.com/grasmash))
- Adding setup:settings target. [\#451](https://github.com/acquia/blt/pull/451) ([grasmash](https://github.com/grasmash))
- Hiding subtargets from blt command list. [\#449](https://github.com/acquia/blt/pull/449) ([grasmash](https://github.com/grasmash))
- Running all drush commands from docroot in case alias is unset. [\#448](https://github.com/acquia/blt/pull/448) ([grasmash](https://github.com/grasmash))
- Add documentation covering wikimedia/composer-merge-plugin. [\#447](https://github.com/acquia/blt/pull/447) ([jrbeeman](https://github.com/jrbeeman))
- Adding more files to deprecated files list for cleanup command. [\#445](https://github.com/acquia/blt/pull/445) ([grasmash](https://github.com/grasmash))
- Removing composer checks from BLT. Composer is assumed. [\#444](https://github.com/acquia/blt/pull/444) ([grasmash](https://github.com/grasmash))
- Adding VM config checks to the doctor. [\#443](https://github.com/acquia/blt/pull/443) ([grasmash](https://github.com/grasmash))
- Remove root detection to allow drush to run remotely [\#438](https://github.com/acquia/blt/pull/438) ([steveworley](https://github.com/steveworley))
- Making doctor command more verbose for checking correct config. [\#437](https://github.com/acquia/blt/pull/437) ([grasmash](https://github.com/grasmash))
- Adding metadata to composer.json. [\#436](https://github.com/acquia/blt/pull/436) ([grasmash](https://github.com/grasmash))
- Prompt for deploy properties [\#434](https://github.com/acquia/blt/pull/434) ([steveworley](https://github.com/steveworley))
- Adding ascii art to blt command list. [\#433](https://github.com/acquia/blt/pull/433) ([grasmash](https://github.com/grasmash))

**Fixed bugs:**

- Fixing bug in Doctor's VM config check. [\#465](https://github.com/acquia/blt/pull/465) ([grasmash](https://github.com/grasmash))
- Pinning Drupal Console to 1.0.0-beta5 to prevent bug in RC1. [\#463](https://github.com/acquia/blt/pull/463) ([grasmash](https://github.com/grasmash))
- Passes repo.root into filterFileListByFileSet target [\#446](https://github.com/acquia/blt/pull/446) ([steveworley](https://github.com/steveworley))
- Update blt.settings.php ACE/ACSF logic [\#431](https://github.com/acquia/blt/pull/431) ([dpagini](https://github.com/dpagini))

**Misc merged pull requests**

- Updating docs to indicate PHP BZ2 extension requirement. [\#464](https://github.com/acquia/blt/pull/464) ([grasmash](https://github.com/grasmash))
- Temporarily disabling Lightning Behat tests. [\#461](https://github.com/acquia/blt/pull/461) ([grasmash](https://github.com/grasmash))
- Cleaned up onboarding docs. [\#435](https://github.com/acquia/blt/pull/435) ([danepowell](https://github.com/danepowell))

## [8.4.5](https://github.com/acquia/blt/tree/8.4.5) (2016-09-20)
[Full Changelog](https://github.com/acquia/blt/compare/8.4.4...8.4.5)

**Implemented enhancements:**

- Suggesting hirak/prestissimo in composer.json. [\#430](https://github.com/acquia/blt/pull/430) ([grasmash](https://github.com/grasmash))
- Improving doctor output on brand-new sites. [\#429](https://github.com/acquia/blt/pull/429) ([grasmash](https://github.com/grasmash))

## [8.4.4](https://github.com/acquia/blt/tree/8.4.4) (2016-09-20)
[Full Changelog](https://github.com/acquia/blt/compare/8.4.3...8.4.4)

**Implemented enhancements:**

- Change order of running updates and config imports [\#419](https://github.com/acquia/blt/issues/419)
- Adding check for blt.settings.php in factory hooks to doctor. [\#428](https://github.com/acquia/blt/pull/428) ([grasmash](https://github.com/grasmash))
- Delete protect\_env.php.example [\#422](https://github.com/acquia/blt/pull/422) ([dpagini](https://github.com/dpagini))
- 419: Added another Config import before Database updates are executed. [\#420](https://github.com/acquia/blt/pull/420) ([vaibhavjain-in](https://github.com/vaibhavjain-in))
- Expanding doctor command. [\#417](https://github.com/acquia/blt/pull/417) ([grasmash](https://github.com/grasmash))
- Adding doctor command. [\#415](https://github.com/acquia/blt/pull/415) ([grasmash](https://github.com/grasmash))
- Update filesystem.settings.php for ACSF [\#411](https://github.com/acquia/blt/pull/411) ([dpagini](https://github.com/dpagini))

**Fixed bugs:**

- README from blt-project is created on project creation [\#421](https://github.com/acquia/blt/issues/421)
- Fixes \#423: Prevent overwrite of factory-hooks and FeatureContext.php. [\#427](https://github.com/acquia/blt/pull/427) ([grasmash](https://github.com/grasmash))
- Fixing blt symlink for deployment testing. [\#425](https://github.com/acquia/blt/pull/425) ([grasmash](https://github.com/grasmash))
- Bugfix for ACSF db name. [\#418](https://github.com/acquia/blt/pull/418) ([lcatlett](https://github.com/lcatlett))

**Closed issues:**

- Updating BLT with composer [\#423](https://github.com/acquia/blt/issues/423)

## [8.4.3](https://github.com/acquia/blt/tree/8.4.3) (2016-09-15)
[Full Changelog](https://github.com/acquia/blt/compare/8.4.2...8.4.3)

**Implemented enhancements:**

- Optimizing autoload for artifact. [\#409](https://github.com/acquia/blt/pull/409) ([grasmash](https://github.com/grasmash))
- Separating setup:drush from setup:drupal:settings. [\#407](https://github.com/acquia/blt/pull/407) ([grasmash](https://github.com/grasmash))
- Adding test for Pipelines integration. [\#406](https://github.com/acquia/blt/pull/406) ([grasmash](https://github.com/grasmash))
- Update behat/mink to ~1.7 \(v1.6.0 -\> v1.7.1\), upgrade lightning to ~8 \(1.03 -\> 1.04\) [\#405](https://github.com/acquia/blt/pull/405) ([balsama](https://github.com/balsama))
- Installing alias automatically. [\#404](https://github.com/acquia/blt/pull/404) ([grasmash](https://github.com/grasmash))
- Removing blt init from more places. [\#403](https://github.com/acquia/blt/pull/403) ([grasmash](https://github.com/grasmash))

**Fixed bugs:**

- Adding patches dir back to artifact. [\#402](https://github.com/acquia/blt/pull/402) ([grasmash](https://github.com/grasmash))
- Removing POST\_INSTALL\_CMD event from BLT composer plugin. [\#401](https://github.com/acquia/blt/pull/401) ([grasmash](https://github.com/grasmash))

**Misc merged pull requests**

- Updating release instructions. [\#410](https://github.com/acquia/blt/pull/410) ([grasmash](https://github.com/grasmash))
- Resolve typo in install docs. [\#400](https://github.com/acquia/blt/pull/400) ([srowlands](https://github.com/srowlands))

## [8.4.2](https://github.com/acquia/blt/tree/8.4.2) (2016-09-15)
[Full Changelog](https://github.com/acquia/blt/compare/8.4.1...8.4.2)

**Implemented enhancements:**

- Create init tasks for ci [\#379](https://github.com/acquia/blt/issues/379)
- Allow customization of blt update file excludes. [\#396](https://github.com/acquia/blt/pull/396) ([grasmash](https://github.com/grasmash))
- Updating Phing to latest version. [\#395](https://github.com/acquia/blt/pull/395) ([grasmash](https://github.com/grasmash))
- Removing duplicative drupal scaffold files. [\#391](https://github.com/acquia/blt/pull/391) ([grasmash](https://github.com/grasmash))
- Ignoring various files in rsync update. [\#390](https://github.com/acquia/blt/pull/390) ([grasmash](https://github.com/grasmash))
- Fixes \#379: Adding init tasks for ci config. [\#389](https://github.com/acquia/blt/pull/389) ([grasmash](https://github.com/grasmash))
- Making deprecated file deletion opt-in. [\#388](https://github.com/acquia/blt/pull/388) ([grasmash](https://github.com/grasmash))

**Fixed bugs:**

- Update ACSF hooks to use new blt vendor settings. [\#393](https://github.com/acquia/blt/pull/393) ([dpagini](https://github.com/dpagini))
- fix template/.gitattributes [\#392](https://github.com/acquia/blt/pull/392) ([dpagini](https://github.com/dpagini))
- Adding quotes to Behat tags. [\#387](https://github.com/acquia/blt/pull/387) ([grasmash](https://github.com/grasmash))

**Misc merged pull requests**

- Adding tests and docs for travis:ci:init command. [\#398](https://github.com/acquia/blt/pull/398) ([grasmash](https://github.com/grasmash))
- Simple grammar update to add a space. [\#397](https://github.com/acquia/blt/pull/397) ([dpagini](https://github.com/dpagini))
- BLT: Encourage cache clear upon project creation. [\#394](https://github.com/acquia/blt/pull/394) ([bhasselbeck](https://github.com/bhasselbeck))
- Removing invaid @todo. [\#386](https://github.com/acquia/blt/pull/386) ([grasmash](https://github.com/grasmash))

## [8.4.1](https://github.com/acquia/blt/tree/8.4.1) (2016-09-12)
[Full Changelog](https://github.com/acquia/blt/compare/8.4.0...8.4.1)

**Implemented enhancements:**

- BLT presumes sites/default, complicates multisite settings [\#380](https://github.com/acquia/blt/issues/380)
- Automate execution of `blt update` after composer update [\#341](https://github.com/acquia/blt/issues/341)
- Install BLT alias automatically for developers [\#284](https://github.com/acquia/blt/issues/284)
- Fixes \#380: Allow $site\_dir to be defined for multisite. [\#382](https://github.com/acquia/blt/pull/382) ([grasmash](https://github.com/grasmash))
- Making vm:init task more verbose. [\#376](https://github.com/acquia/blt/pull/376) ([grasmash](https://github.com/grasmash))
- Replacing external Phing random string task with internal one. [\#375](https://github.com/acquia/blt/pull/375) ([grasmash](https://github.com/grasmash))
- Added search modules [\#374](https://github.com/acquia/blt/pull/374) ([danepowell](https://github.com/danepowell))
- Chmod settings.php to 644 instead of 755 [\#370](https://github.com/acquia/blt/pull/370) ([bkosborne](https://github.com/bkosborne))
- Fixes \#341 \#284: Automating alias installation and template updates. [\#368](https://github.com/acquia/blt/pull/368) ([grasmash](https://github.com/grasmash))

**Fixed bugs:**

- fatal: A branch named 'master-build' already exists. [\#381](https://github.com/acquia/blt/issues/381)
- Fixing multiple deployment target bug. [\#373](https://github.com/acquia/blt/pull/373) ([grasmash](https://github.com/grasmash))
- Revert "Modify deploy phing to enable multiple environment deployments." [\#372](https://github.com/acquia/blt/pull/372) ([grasmash](https://github.com/grasmash))
- Modify deploy phing to enable multiple environment deployments. [\#363](https://github.com/acquia/blt/pull/363) ([marksakurada](https://github.com/marksakurada))

**Closed issues:**

- Move all common settings files to `sites/all/settings` [\#147](https://github.com/acquia/blt/issues/147)

**Misc merged pull requests**

- Updating documentation. [\#383](https://github.com/acquia/blt/pull/383) ([grasmash](https://github.com/grasmash))
- Update RELEASE.md [\#378](https://github.com/acquia/blt/pull/378) ([grasmash](https://github.com/grasmash))
- Updating CONTRIBUTING.md and RELEASE.md [\#371](https://github.com/acquia/blt/pull/371) ([grasmash](https://github.com/grasmash))

## [8.4.0](https://github.com/acquia/blt/tree/8.4.0) (2016-09-09)
[Full Changelog](https://github.com/acquia/blt/compare/8.3.4...8.4.0)

**Implemented enhancements:**

- local-sync.xml tasks should use drush instead of exec and aliases [\#56](https://github.com/acquia/blt/issues/56)
- Bolt should adhere to verbose options and not hardcode them [\#36](https://github.com/acquia/blt/issues/36)
- Removing extraneous base.settings.php. [\#367](https://github.com/acquia/blt/pull/367) ([grasmash](https://github.com/grasmash))
- Fixes \#36, \#56: Making DrushTask conform to Phing verbosity. Converting \<exec\> instances to \<drush\>. [\#366](https://github.com/acquia/blt/pull/366) ([grasmash](https://github.com/grasmash))
- Making repetitive messages less verbose. [\#365](https://github.com/acquia/blt/pull/365) ([grasmash](https://github.com/grasmash))
- Moving default settings files out of template. [\#364](https://github.com/acquia/blt/pull/364) ([grasmash](https://github.com/grasmash))
- Adding patches and tmp to deploy excludes [\#362](https://github.com/acquia/blt/pull/362) ([dpagini](https://github.com/dpagini))

## [8.3.4](https://github.com/acquia/blt/tree/8.3.4) (2016-09-02)
[Full Changelog](https://github.com/acquia/blt/compare/8.3.3...8.3.4)

**Implemented enhancements:**

- BLT doesn't define file system storage intelligently [\#211](https://github.com/acquia/blt/issues/211)
- Fixes \#211: Store filepaths intelligently. [\#359](https://github.com/acquia/blt/pull/359) ([grasmash](https://github.com/grasmash))
- Add configuration for filesystem settings. \(\#211\) [\#322](https://github.com/acquia/blt/pull/322) ([pixlkat](https://github.com/pixlkat))
- Resolves \#44: Register lint:twig console command. [\#297](https://github.com/acquia/blt/pull/297) ([grasmash](https://github.com/grasmash))

**Fixed bugs:**

- drush problem with deploy:acsf:init [\#356](https://github.com/acquia/blt/issues/356)
- VM Initialization \(./blt.sh vm:init\) freezes in terminal when copying drush aliases [\#241](https://github.com/acquia/blt/issues/241)
- Fixing bug in `blt init` [\#360](https://github.com/acquia/blt/pull/360) ([grasmash](https://github.com/grasmash))
- Fixes \#356: deploy:acsf:init uses wrong docroot. [\#357](https://github.com/acquia/blt/pull/357) ([grasmash](https://github.com/grasmash))

**Misc merged pull requests**

- Fix the setup amp stack anchor. [\#355](https://github.com/acquia/blt/pull/355) ([naveenvalecha](https://github.com/naveenvalecha))
- Adding CHANGELOG.md and generator settings. [\#354](https://github.com/acquia/blt/pull/354) ([grasmash](https://github.com/grasmash))

## [8.3.3](https://github.com/acquia/blt/tree/8.3.3) (2016-09-01)
[Full Changelog](https://github.com/acquia/blt/compare/8.3.2...8.3.3)

**Implemented enhancements:**

- Move tests/phpunit/Bolt tests into /tests [\#295](https://github.com/acquia/blt/issues/295)
- Completing pipelines build file. [\#351](https://github.com/acquia/blt/pull/351) ([grasmash](https://github.com/grasmash))
- Tweaking verbosity. [\#349](https://github.com/acquia/blt/pull/349) ([grasmash](https://github.com/grasmash))
- Increasing verbosity of all exec tasks. [\#347](https://github.com/acquia/blt/pull/347) ([grasmash](https://github.com/grasmash))

**Fixed bugs:**

- Configuration written to settings.php during install [\#345](https://github.com/acquia/blt/issues/345)
- Issue \#345: Changing require\_once\(\) to require\(\). [\#346](https://github.com/acquia/blt/pull/346) ([grasmash](https://github.com/grasmash))

**Misc merged pull requests**

- Switch -Dbehat.param to path in single behat test example. [\#353](https://github.com/acquia/blt/pull/353) ([seanpclark](https://github.com/seanpclark))

## [8.3.2](https://github.com/acquia/blt/tree/8.3.2) (2016-08-31)
[Full Changelog](https://github.com/acquia/blt/compare/8.3.1...8.3.2)

**Implemented enhancements:**

- NPM management of deployed front-end libraries [\#333](https://github.com/acquia/blt/issues/333)
- Add nodejs frontend tools for drupalvm integration [\#324](https://github.com/acquia/blt/issues/324)
- Removing DevDesktop settings include. [\#343](https://github.com/acquia/blt/pull/343) ([grasmash](https://github.com/grasmash))
- Set perms on settings files. [\#342](https://github.com/acquia/blt/pull/342) ([danepowell](https://github.com/danepowell))
- Adding an update.sh script. [\#339](https://github.com/acquia/blt/pull/339) ([grasmash](https://github.com/grasmash))
- Issue \#333: Allow deployment of front-end libraries. [\#334](https://github.com/acquia/blt/pull/334) ([danepowell](https://github.com/danepowell))

**Fixed bugs:**

- override blt phing target [\#316](https://github.com/acquia/blt/issues/316)
- drush.wrapper broken by xdebug message [\#315](https://github.com/acquia/blt/issues/315)
- PHPUnit Tests should load project.local.yml [\#309](https://github.com/acquia/blt/issues/309)
- Removing Phantom JS CDN URL. [\#340](https://github.com/acquia/blt/pull/340) ([grasmash](https://github.com/grasmash))
- Fixes \#315: Resolving xdebug and drush.wrapper bug. [\#337](https://github.com/acquia/blt/pull/337) ([grasmash](https://github.com/grasmash))
- Ignoring a lightning patch. [\#335](https://github.com/acquia/blt/pull/335) ([grasmash](https://github.com/grasmash))

**Closed issues:**

- Clean up the Continuous Integration instructions [\#319](https://github.com/acquia/blt/issues/319)

**Misc merged pull requests**

- The URL in template README.md is malformed [\#336](https://github.com/acquia/blt/pull/336) ([bhasselbeck](https://github.com/bhasselbeck))
- Added tips on features wrappers. [\#330](https://github.com/acquia/blt/pull/330) ([danepowell](https://github.com/danepowell))
- Setup and deploy should install frontend dependencies. [\#327](https://github.com/acquia/blt/pull/327) ([danepowell](https://github.com/danepowell))
- Felt backwards to me. [\#326](https://github.com/acquia/blt/pull/326) ([justinlevi](https://github.com/justinlevi))
- Githiub BLT Issue \#324: Includes NodeJS tools as well as php-bcmath… [\#325](https://github.com/acquia/blt/pull/325) ([bhasselbeck](https://github.com/bhasselbeck))
- \#319 - Cleaning up ci.md [\#320](https://github.com/acquia/blt/pull/320) ([webkenny](https://github.com/webkenny))
- Updated editorconfig for composer.json. [\#318](https://github.com/acquia/blt/pull/318) ([danepowell](https://github.com/danepowell))
- Allow overrides of blt phing targets: fixes \#316. [\#317](https://github.com/acquia/blt/pull/317) ([dpagini](https://github.com/dpagini))
- Move phpunit tests \(\#295\) [\#313](https://github.com/acquia/blt/pull/313) ([pixlkat](https://github.com/pixlkat))
- PHPUnit/TestBase.php import project.local.yml overrides [\#311](https://github.com/acquia/blt/pull/311) ([dpagini](https://github.com/dpagini))
- Fixing working directory for drush tasks. [\#310](https://github.com/acquia/blt/pull/310) ([grasmash](https://github.com/grasmash))
- Simplifying drupal vm aliases. [\#308](https://github.com/acquia/blt/pull/308) ([grasmash](https://github.com/grasmash))
- Adding pipelines WIP. [\#307](https://github.com/acquia/blt/pull/307) ([grasmash](https://github.com/grasmash))
- Move git hooks [\#306](https://github.com/acquia/blt/pull/306) ([grasmash](https://github.com/grasmash))
- Document how to handle config and content conflicts. [\#305](https://github.com/acquia/blt/pull/305) ([danepowell](https://github.com/danepowell))
- Moving git-hooks out of template. [\#304](https://github.com/acquia/blt/pull/304) ([grasmash](https://github.com/grasmash))
- Add documentation for running a single behat test with phing. [\#286](https://github.com/acquia/blt/pull/286) ([marksakurada](https://github.com/marksakurada))

## [8.3.1](https://github.com/acquia/blt/tree/8.3.1) (2016-08-16)
[Full Changelog](https://github.com/acquia/blt/compare/8.3.0...8.3.1)

**Closed issues:**

- PhantomJS installation fails 403 Issue. [\#223](https://github.com/acquia/blt/issues/223)
- Allow separate setup and build frontend tasks [\#221](https://github.com/acquia/blt/issues/221)
- When using VM built with vm:init, NFS synced folder doesn't always mount correctly [\#214](https://github.com/acquia/blt/issues/214)
- Remove architecture.md [\#204](https://github.com/acquia/blt/issues/204)
- Travis CI Automated Deployment Problems [\#196](https://github.com/acquia/blt/issues/196)
- Simplify front end file inclusion [\#146](https://github.com/acquia/blt/issues/146)
- Difference between PHPUnit Tests included with Bolt versus PHPUnit tests in my custom module [\#143](https://github.com/acquia/blt/issues/143)
- Site installs crash if files directory is not empty [\#128](https://github.com/acquia/blt/issues/128)
- Installation script fails when using VM and configuration directory exists [\#64](https://github.com/acquia/blt/issues/64)

**Misc merged pull requests**

- Resolves \#204: Removing architecture.md. [\#303](https://github.com/acquia/blt/pull/303) ([grasmash](https://github.com/grasmash))
- Improving upgrade path. [\#302](https://github.com/acquia/blt/pull/302) ([grasmash](https://github.com/grasmash))
- Removing composer-patches fork. [\#301](https://github.com/acquia/blt/pull/301) ([grasmash](https://github.com/grasmash))
- Fixing bug in docs deletion. [\#300](https://github.com/acquia/blt/pull/300) ([grasmash](https://github.com/grasmash))
- Replaced require with require\_once in settings.php and blt.settings.php [\#298](https://github.com/acquia/blt/pull/298) ([aweingarten](https://github.com/aweingarten))
- Resolves \#196: Travis CI documentation. [\#293](https://github.com/acquia/blt/pull/293) ([grasmash](https://github.com/grasmash))
- Updating phantomjs-installer to 2.x. [\#292](https://github.com/acquia/blt/pull/292) ([grasmash](https://github.com/grasmash))
- Resolves \#221: Adding frontend-setup target-hook. [\#291](https://github.com/acquia/blt/pull/291) ([grasmash](https://github.com/grasmash))
- Resolves \#146: Simplify frontend file inclusion. [\#289](https://github.com/acquia/blt/pull/289) ([grasmash](https://github.com/grasmash))
- Update top-level .editorconfig to include composer.json indent size. [\#287](https://github.com/acquia/blt/pull/287) ([jrbeeman](https://github.com/jrbeeman))
- \[BUGFIX\] Tests may fail due to PhantomJs not ready [\#283](https://github.com/acquia/blt/pull/283) ([mickaelperrin](https://github.com/mickaelperrin))
- Updated features doc. [\#281](https://github.com/acquia/blt/pull/281) ([danepowell](https://github.com/danepowell))

## [8.3.0](https://github.com/acquia/blt/tree/8.3.0) (2016-08-11)
[Full Changelog](https://github.com/acquia/blt/compare/8.2.0...8.3.0)

**Implemented enhancements:**

- Make -r in drush.wrapper read from drush.root in project.yml [\#262](https://github.com/acquia/blt/issues/262)
- Get project prefix from project.yml for git-commit hook [\#249](https://github.com/acquia/blt/issues/249)

**Fixed bugs:**

- TravisCI installs failing [\#273](https://github.com/acquia/blt/issues/273)
- The local:refresh task overwrites local.settings.php [\#248](https://github.com/acquia/blt/issues/248)
- Issue updating BLT [\#245](https://github.com/acquia/blt/issues/245)

**Closed issues:**

- Documentation Improvements [\#261](https://github.com/acquia/blt/issues/261)
- Document how to run a partial set of tests [\#243](https://github.com/acquia/blt/issues/243)
- Patches pulled from Drupal.org's core issue queue do not apply against drupal/core Composer dependency [\#240](https://github.com/acquia/blt/issues/240)
- Can't use composer to require new modules [\#238](https://github.com/acquia/blt/issues/238)
- Document how to modify deploy excludes [\#237](https://github.com/acquia/blt/issues/237)
- Can BLT projects be built without Lightning? [\#235](https://github.com/acquia/blt/issues/235)
- Cloud hook permissions are not being passed to the deployed artifact. [\#233](https://github.com/acquia/blt/issues/233)
- Force certain directories when deploying to Acquia Cloud [\#227](https://github.com/acquia/blt/issues/227)
- Improve composer documentation [\#226](https://github.com/acquia/blt/issues/226)
- Document lightning requirement for npm [\#220](https://github.com/acquia/blt/issues/220)
- Document how to commit dependencies [\#219](https://github.com/acquia/blt/issues/219)
- Local setup fails on Drupal\Core\Installer\Exception\AlreadyInstalledException + Contains unmentioned dependencies [\#218](https://github.com/acquia/blt/issues/218)
- Investigate converting BLT into a composer package [\#213](https://github.com/acquia/blt/issues/213)
- why is composer.json using packagist instead of https://packages.drupal.org/8?  [\#187](https://github.com/acquia/blt/issues/187)
- Patch failures should cause composer install to fail [\#183](https://github.com/acquia/blt/issues/183)
- Document using Behat with Drupal VM [\#176](https://github.com/acquia/blt/issues/176)
- ./blt.sh blt:update error - Update seems broken on Windows & Mac [\#171](https://github.com/acquia/blt/issues/171)
- composer.json install-path for custom module hosted externally? [\#170](https://github.com/acquia/blt/issues/170)
- Git PHPUnit tests take a reeeeally long time [\#166](https://github.com/acquia/blt/issues/166)
- Drupal VM Build Failure when using deafult box/config.yml [\#161](https://github.com/acquia/blt/issues/161)
- Rename build dir [\#160](https://github.com/acquia/blt/issues/160)
- Some files are not removed from build artifact [\#157](https://github.com/acquia/blt/issues/157)
- Support alternate front end build tasks [\#154](https://github.com/acquia/blt/issues/154)
- project.acquia\_subname should be defined or otherwise replaced in project.yml [\#139](https://github.com/acquia/blt/issues/139)
- Permission Denied on blt bash alias creation [\#133](https://github.com/acquia/blt/issues/133)
- Command site-install needs a higher bootstrap level to run - you will need to invoke drush from a more functional Drupal environment to run this command. [\#132](https://github.com/acquia/blt/issues/132)
- Incorrect link [\#118](https://github.com/acquia/blt/issues/118)
- Create's output 'next instructions' are out of date [\#109](https://github.com/acquia/blt/issues/109)
- Cannot Write Settings.php  [\#103](https://github.com/acquia/blt/issues/103)
- Build failing on drush alias [\#101](https://github.com/acquia/blt/issues/101)
- Add test coverage for ACSF configuration. [\#90](https://github.com/acquia/blt/issues/90)
- grasmash/phing composer dependency Build Failure [\#88](https://github.com/acquia/blt/issues/88)
- Enable local Twig Debugging [\#85](https://github.com/acquia/blt/issues/85)
- \[RFC\] Change the name to avoid confusion with the CMS named Bolt. [\#81](https://github.com/acquia/blt/issues/81)
- Avoiding GitHub rate limiting [\#70](https://github.com/acquia/blt/issues/70)
- Make vendor name configurable [\#67](https://github.com/acquia/blt/issues/67)
- Distinction needed between 'CI' environment and 'local' environment [\#52](https://github.com/acquia/blt/issues/52)
- ACSF does not get hash salt set [\#46](https://github.com/acquia/blt/issues/46)
- Running setup:bolt:update has error [\#45](https://github.com/acquia/blt/issues/45)
- Provide default services.yml, including APCu config [\#40](https://github.com/acquia/blt/issues/40)
- Document overriding Phing variable values [\#24](https://github.com/acquia/blt/issues/24)
- Improve DX of project creation [\#23](https://github.com/acquia/blt/issues/23)
- Generating a deployment artifact for ACE is slow [\#22](https://github.com/acquia/blt/issues/22)
- Example factory hooks. [\#21](https://github.com/acquia/blt/issues/21)
- How do you use standard/minimal core profiles? [\#20](https://github.com/acquia/blt/issues/20)
- Typo in deploy:artifact:add-remote usage. [\#16](https://github.com/acquia/blt/issues/16)
- PHPUnit Drush test should use the actual local URL [\#12](https://github.com/acquia/blt/issues/12)
- PHPUnit Git tests shouldn't create actual commits [\#11](https://github.com/acquia/blt/issues/11)
- composer install is run twice on initial setup [\#8](https://github.com/acquia/blt/issues/8)
- Switch documentation to not use line breaks at 80 cols [\#4](https://github.com/acquia/blt/issues/4)

**Misc merged pull requests**

- Resolves \#243: Document how to run a partial set of tests. [\#282](https://github.com/acquia/blt/pull/282) ([grasmash](https://github.com/grasmash))
- Resolves \#219: Documenting committing dependencies. [\#280](https://github.com/acquia/blt/pull/280) ([grasmash](https://github.com/grasmash))
- Resolves \#226: Adding composer docs. [\#278](https://github.com/acquia/blt/pull/278) ([grasmash](https://github.com/grasmash))
- Resolves \#237: Document deploy excludes. [\#277](https://github.com/acquia/blt/pull/277) ([grasmash](https://github.com/grasmash))
- Making drush docroot smarter. [\#276](https://github.com/acquia/blt/pull/276) ([grasmash](https://github.com/grasmash))
- Moving docs to readme. [\#275](https://github.com/acquia/blt/pull/275) ([grasmash](https://github.com/grasmash))
- Adding blt extension docs. [\#272](https://github.com/acquia/blt/pull/272) ([grasmash](https://github.com/grasmash))
- \[BUGFIX\] Failed Behat tests keeps pahntomJS running [\#271](https://github.com/acquia/blt/pull/271) ([mickaelperrin](https://github.com/mickaelperrin))
- Mkdocs [\#270](https://github.com/acquia/blt/pull/270) ([grasmash](https://github.com/grasmash))
- Fixed broken git commit msg hook. [\#269](https://github.com/acquia/blt/pull/269) ([danepowell](https://github.com/danepowell))
- Mkdocs [\#268](https://github.com/acquia/blt/pull/268) ([grasmash](https://github.com/grasmash))
- Mkdocs [\#267](https://github.com/acquia/blt/pull/267) ([grasmash](https://github.com/grasmash))
- Updating mkdocs to fix build errors. [\#266](https://github.com/acquia/blt/pull/266) ([grasmash](https://github.com/grasmash))
- Replace blt sh mentions with blt alias. [\#263](https://github.com/acquia/blt/pull/263) ([ChuChuNaKu](https://github.com/ChuChuNaKu))
- Updating Drupal Console. [\#258](https://github.com/acquia/blt/pull/258) ([grasmash](https://github.com/grasmash))
- Updating deploy excludes. [\#257](https://github.com/acquia/blt/pull/257) ([grasmash](https://github.com/grasmash))
- Resolves \#249: Adding yaml parser to git-commit hook. [\#256](https://github.com/acquia/blt/pull/256) ([grasmash](https://github.com/grasmash))
- Update repo-architecture.md [\#239](https://github.com/acquia/blt/pull/239) ([rhuffstedtler](https://github.com/rhuffstedtler))
- Removing undocumented bower dependency. [\#236](https://github.com/acquia/blt/pull/236) ([grasmash](https://github.com/grasmash))
- Issue 233: Removing --no-p option from rsync command in deploy:copy task [\#234](https://github.com/acquia/blt/pull/234) ([msherron](https://github.com/msherron))
- Remove scripts/drupal directory from blt/update-scaffold [\#232](https://github.com/acquia/blt/pull/232) ([pixlkat](https://github.com/pixlkat))
- Making security test failure more verbose. [\#231](https://github.com/acquia/blt/pull/231) ([grasmash](https://github.com/grasmash))
- Updating drupal-scaffold. [\#230](https://github.com/acquia/blt/pull/230) ([grasmash](https://github.com/grasmash))
- \#227 Force certain directories when deploying to Acquia Cloud. [\#228](https://github.com/acquia/blt/pull/228) ([webkenny](https://github.com/webkenny))
- Update release-process.md to fix typo [\#225](https://github.com/acquia/blt/pull/225) ([kmbremner](https://github.com/kmbremner))
- \[console\] Update Drupal Console to 1.0 ver. [\#224](https://github.com/acquia/blt/pull/224) ([jmolivas](https://github.com/jmolivas))
- Update includes.settings.php [\#222](https://github.com/acquia/blt/pull/222) ([janaksingh](https://github.com/janaksingh))
- Converting BLT to composer package. [\#217](https://github.com/acquia/blt/pull/217) ([grasmash](https://github.com/grasmash))
- Ignoring Behat tests with PHPCS, adding style-guide dir exemption. [\#216](https://github.com/acquia/blt/pull/216) ([grasmash](https://github.com/grasmash))
- Fixes \#214: Fix default Drupal VM synced\_folder path doc. [\#215](https://github.com/acquia/blt/pull/215) ([geerlingguy](https://github.com/geerlingguy))
- Don't overwrite local config files [\#210](https://github.com/acquia/blt/pull/210) ([gapple](https://github.com/gapple))
- Adding docroot to drush.wrapper. [\#209](https://github.com/acquia/blt/pull/209) ([grasmash](https://github.com/grasmash))
- Fixed dev env detection on ACSF. [\#207](https://github.com/acquia/blt/pull/207) ([danepowell](https://github.com/danepowell))
- Fix permission changes to default sites directory contents [\#206](https://github.com/acquia/blt/pull/206) ([gapple](https://github.com/gapple))
- Removing Icon from gitignore [\#205](https://github.com/acquia/blt/pull/205) ([CashWilliams](https://github.com/CashWilliams))
- Ignore custom theme node\_modules folder [\#203](https://github.com/acquia/blt/pull/203) ([justinlevi](https://github.com/justinlevi))
- Language update [\#202](https://github.com/acquia/blt/pull/202) ([justinlevi](https://github.com/justinlevi))
- Fixing prompt for BLT alias. [\#201](https://github.com/acquia/blt/pull/201) ([grasmash](https://github.com/grasmash))
- Subversion needed for ./blt.sh blt:update in VM [\#200](https://github.com/acquia/blt/pull/200) ([justinlevi](https://github.com/justinlevi))
- Fixing up drush.wrapper. [\#199](https://github.com/acquia/blt/pull/199) ([grasmash](https://github.com/grasmash))
- Update composer.json [\#198](https://github.com/acquia/blt/pull/198) ([skippednote](https://github.com/skippednote))
- Correctly detect environments on ACSF. [\#197](https://github.com/acquia/blt/pull/197) ([danepowell](https://github.com/danepowell))
- Adding support for custom commands in frontend and setup targets. [\#195](https://github.com/acquia/blt/pull/195) ([grasmash](https://github.com/grasmash))
- Revert "Adding support for custom commands in frontend and setup targets." [\#194](https://github.com/acquia/blt/pull/194) ([grasmash](https://github.com/grasmash))
- Adding support for custom commands in frontend and setup targets. [\#193](https://github.com/acquia/blt/pull/193) ([grasmash](https://github.com/grasmash))
- Adding composer validation early in Travis build. [\#192](https://github.com/acquia/blt/pull/192) ([grasmash](https://github.com/grasmash))
- Support composer patches. [\#191](https://github.com/acquia/blt/pull/191) ([danepowell](https://github.com/danepowell))
- Adding more excludes for deployments. [\#189](https://github.com/acquia/blt/pull/189) ([grasmash](https://github.com/grasmash))
- Adding docs for using local patches. [\#188](https://github.com/acquia/blt/pull/188) ([grasmash](https://github.com/grasmash))
- Update local-development.md [\#186](https://github.com/acquia/blt/pull/186) ([grasmash](https://github.com/grasmash))
- Composer install should fail on bad patches. [\#185](https://github.com/acquia/blt/pull/185) ([danepowell](https://github.com/danepowell))
- Provide temp files location in default local settings. [\#184](https://github.com/acquia/blt/pull/184) ([CashWilliams](https://github.com/CashWilliams))
- Adding install-phantomjs script for composer. [\#182](https://github.com/acquia/blt/pull/182) ([grasmash](https://github.com/grasmash))
- Ensuring that project.local.yml overrides core yml values. [\#181](https://github.com/acquia/blt/pull/181) ([grasmash](https://github.com/grasmash))
- Minor Typo fix and updating default selenium port [\#178](https://github.com/acquia/blt/pull/178) ([justinlevi](https://github.com/justinlevi))
- Update local-development.md [\#177](https://github.com/acquia/blt/pull/177) ([grasmash](https://github.com/grasmash))
- Tweaking deploy excludes. [\#175](https://github.com/acquia/blt/pull/175) ([grasmash](https://github.com/grasmash))
- Updated update scaffold. [\#173](https://github.com/acquia/blt/pull/173) ([danepowell](https://github.com/danepowell))
- Defining custom docroot for Drupal VM in project.local.yml. [\#172](https://github.com/acquia/blt/pull/172) ([grasmash](https://github.com/grasmash))
- Clarifying load test environments [\#169](https://github.com/acquia/blt/pull/169) ([ghazlewood](https://github.com/ghazlewood))
- Resolves \#166: Speed up GitTest commit-msg checks. [\#168](https://github.com/acquia/blt/pull/168) ([grasmash](https://github.com/grasmash))
- Removing :artifact from deploy targets. [\#167](https://github.com/acquia/blt/pull/167) ([grasmash](https://github.com/grasmash))
- Improve composer validation. [\#165](https://github.com/acquia/blt/pull/165) ([danepowell](https://github.com/danepowell))
- Changing deploy to use rsync. [\#162](https://github.com/acquia/blt/pull/162) ([grasmash](https://github.com/grasmash))
- Allow project.local.yml for different local environments. [\#158](https://github.com/acquia/blt/pull/158) ([damontgomery](https://github.com/damontgomery))
- Minor update scaffold cleanup. [\#156](https://github.com/acquia/blt/pull/156) ([danepowell](https://github.com/danepowell))
- Fixing remote repo value. [\#155](https://github.com/acquia/blt/pull/155) ([grasmash](https://github.com/grasmash))
- Workin on BLT deploys. \(\#152\) [\#153](https://github.com/acquia/blt/pull/153) ([grasmash](https://github.com/grasmash))
- Workin on BLT deploys. [\#152](https://github.com/acquia/blt/pull/152) ([grasmash](https://github.com/grasmash))
- Don't fail on chmod. [\#151](https://github.com/acquia/blt/pull/151) ([danepowell](https://github.com/danepowell))
- Cleaning vendor dir of deployment artifact. [\#150](https://github.com/acquia/blt/pull/150) ([grasmash](https://github.com/grasmash))
- Adding deploy.dryRun param for deploy:artifact target. [\#149](https://github.com/acquia/blt/pull/149) ([grasmash](https://github.com/grasmash))
- Adding PHP Bz2 to Drupal VM config. [\#145](https://github.com/acquia/blt/pull/145) ([grasmash](https://github.com/grasmash))
- Fixing failing Behat tests in child project. [\#144](https://github.com/acquia/blt/pull/144) ([grasmash](https://github.com/grasmash))
- Improving VM instructions. [\#142](https://github.com/acquia/blt/pull/142) ([grasmash](https://github.com/grasmash))
- Created common settings includes file. [\#141](https://github.com/acquia/blt/pull/141) ([danepowell](https://github.com/danepowell))
- Make project description configurable. [\#140](https://github.com/acquia/blt/pull/140) ([greylabel](https://github.com/greylabel))
- Update INSTALL.md [\#138](https://github.com/acquia/blt/pull/138) ([haynescw](https://github.com/haynescw))
- Making chmod on site/default optional. [\#137](https://github.com/acquia/blt/pull/137) ([grasmash](https://github.com/grasmash))
- Updated features doc. [\#136](https://github.com/acquia/blt/pull/136) ([danepowell](https://github.com/danepowell))
- Update php minimum requirement [\#135](https://github.com/acquia/blt/pull/135) ([skippednote](https://github.com/skippednote))
- Improving output of blt:alias. [\#134](https://github.com/acquia/blt/pull/134) ([grasmash](https://github.com/grasmash))
- Isolating Bolt PHPunit tests in Bolt subdir. [\#131](https://github.com/acquia/blt/pull/131) ([grasmash](https://github.com/grasmash))
- Updating default behat config for bolt updates. [\#130](https://github.com/acquia/blt/pull/130) ([grasmash](https://github.com/grasmash))
- Adding a default value for drush.default\_alias. [\#129](https://github.com/acquia/blt/pull/129) ([grasmash](https://github.com/grasmash))
- Make phpcs test 1st for faster test fail [\#127](https://github.com/acquia/blt/pull/127) ([grasmash](https://github.com/grasmash))
- Adding more detailed instructions to log output. [\#126](https://github.com/acquia/blt/pull/126) ([grasmash](https://github.com/grasmash))
- DX improvement by adding composer install to blt.sh [\#125](https://github.com/acquia/blt/pull/125) ([grasmash](https://github.com/grasmash))
- Adding local.protocol and local.hostname. [\#122](https://github.com/acquia/blt/pull/122) ([grasmash](https://github.com/grasmash))
- Rermoving unneeded files from update scripts. [\#121](https://github.com/acquia/blt/pull/121) ([grasmash](https://github.com/grasmash))
- Updating .gitignore, adding drupal console. [\#120](https://github.com/acquia/blt/pull/120) ([grasmash](https://github.com/grasmash))
- Specifying config dir in setup:drupal:install. [\#119](https://github.com/acquia/blt/pull/119) ([grasmash](https://github.com/grasmash))
- Allowing delete prompt to be skipped if do.abort = y. [\#115](https://github.com/acquia/blt/pull/115) ([grasmash](https://github.com/grasmash))
- build:validate:test is deprecated. [\#114](https://github.com/acquia/blt/pull/114) ([danepowell](https://github.com/danepowell))
- Support ACSF vanity domains. [\#112](https://github.com/acquia/blt/pull/112) ([danepowell](https://github.com/danepowell))
- Updating instructions output by create command. [\#110](https://github.com/acquia/blt/pull/110) ([grasmash](https://github.com/grasmash))
- Removed the now deleted deploy directory from the update-scaffold scr… [\#108](https://github.com/acquia/blt/pull/108) ([aweingarten](https://github.com/aweingarten))
- Fixing incorrect references to bolt. [\#107](https://github.com/acquia/blt/pull/107) ([grasmash](https://github.com/grasmash))
- Update local-development.md [\#106](https://github.com/acquia/blt/pull/106) ([grasmash](https://github.com/grasmash))
- Adding Drupal VM 3.1 integration. [\#105](https://github.com/acquia/blt/pull/105) ([grasmash](https://github.com/grasmash))
- Updating docs for Drupal VM. [\#104](https://github.com/acquia/blt/pull/104) ([grasmash](https://github.com/grasmash))
- Excluding Lightning AJAX Behat tests. [\#102](https://github.com/acquia/blt/pull/102) ([grasmash](https://github.com/grasmash))
- Update README.md to explain acronym [\#97](https://github.com/acquia/blt/pull/97) ([cam8001](https://github.com/cam8001))
- Fixing drush alias bug. [\#96](https://github.com/acquia/blt/pull/96) ([grasmash](https://github.com/grasmash))
- Adding tugboat support. [\#95](https://github.com/acquia/blt/pull/95) ([grasmash](https://github.com/grasmash))
- Forcing only stable versions of Lightning. [\#94](https://github.com/acquia/blt/pull/94) ([grasmash](https://github.com/grasmash))
- Fixed alias script after rename. [\#91](https://github.com/acquia/blt/pull/91) ([danepowell](https://github.com/danepowell))
- Renaming Bolt to BLT. [\#87](https://github.com/acquia/blt/pull/87) ([grasmash](https://github.com/grasmash))
- Enable local Twig debugging [\#86](https://github.com/acquia/blt/pull/86) ([dpagini](https://github.com/dpagini))
- Issue \#52: Splitting local tasks from CI tasks. [\#84](https://github.com/acquia/blt/pull/84) ([grasmash](https://github.com/grasmash))
- Updated composer docs. [\#83](https://github.com/acquia/blt/pull/83) ([danepowell](https://github.com/danepowell))
- Updated license in composer.json. [\#80](https://github.com/acquia/blt/pull/80) ([danepowell](https://github.com/danepowell))
- Use verb in past tense inc commit messages. [\#77](https://github.com/acquia/blt/pull/77) ([alexdesignworks](https://github.com/alexdesignworks))
- Add conditional around front:build so it will work without a theme. [\#76](https://github.com/acquia/blt/pull/76) ([damontgomery](https://github.com/damontgomery))
- Fixing deployments [\#75](https://github.com/acquia/blt/pull/75) ([grasmash](https://github.com/grasmash))
- Fixed Travis deploys. [\#74](https://github.com/acquia/blt/pull/74) ([danepowell](https://github.com/danepowell))
- Test. [\#73](https://github.com/acquia/blt/pull/73) ([grasmash](https://github.com/grasmash))
- Fixed config import on site installs. [\#69](https://github.com/acquia/blt/pull/69) ([danepowell](https://github.com/danepowell))
- Make vendor name configurable. [\#68](https://github.com/acquia/blt/pull/68) ([greylabel](https://github.com/greylabel))
- Fixes an issue where if no front end exists, build fails [\#66](https://github.com/acquia/blt/pull/66) ([kylebrowning](https://github.com/kylebrowning))
- Adding warning when xdebug is enabled. [\#62](https://github.com/acquia/blt/pull/62) ([grasmash](https://github.com/grasmash))
- Run automated tests on live dbs. [\#61](https://github.com/acquia/blt/pull/61) ([danepowell](https://github.com/danepowell))
- Improve Travis CI deployments. [\#60](https://github.com/acquia/blt/pull/60) ([danepowell](https://github.com/danepowell))
- Deploy bug: get the current branch name for the deployment. [\#58](https://github.com/acquia/blt/pull/58) ([damontgomery](https://github.com/damontgomery))
- Documentation on Drush aliases. [\#57](https://github.com/acquia/blt/pull/57) ([danepowell](https://github.com/danepowell))
- Fix formatting for multi-line preformatted text. [\#51](https://github.com/acquia/blt/pull/51) ([geerlingguy](https://github.com/geerlingguy))
- Updated features workflow doc. [\#50](https://github.com/acquia/blt/pull/50) ([danepowell](https://github.com/danepowell))
- Update composer for lightning [\#49](https://github.com/acquia/blt/pull/49) ([damontgomery](https://github.com/damontgomery))
- Make sure SSH key has 4096 bits [\#48](https://github.com/acquia/blt/pull/48) ([geerlingguy](https://github.com/geerlingguy))
- Resolves \#40: Disable APCu caching to prevent memory exhaustion [\#43](https://github.com/acquia/blt/pull/43) ([danepowell](https://github.com/danepowell))
- Removing branch argument from deploy tasks. [\#42](https://github.com/acquia/blt/pull/42) ([grasmash](https://github.com/grasmash))
- Added docs on field management using Features. [\#41](https://github.com/acquia/blt/pull/41) ([danepowell](https://github.com/danepowell))
- Updating Lightning to RC4. [\#39](https://github.com/acquia/blt/pull/39) ([grasmash](https://github.com/grasmash))
- Improving dev desktop settings.php compatibility. [\#37](https://github.com/acquia/blt/pull/37) ([grasmash](https://github.com/grasmash))
- Cleans up spacing in composer.json [\#35](https://github.com/acquia/blt/pull/35) ([kylebrowning](https://github.com/kylebrowning))
- Adding pre-commit hook to setup:bolt:update. [\#33](https://github.com/acquia/blt/pull/33) ([grasmash](https://github.com/grasmash))
- Changing pre-commit phpcs validation to find docroot differently. [\#32](https://github.com/acquia/blt/pull/32) ([grasmash](https://github.com/grasmash))
- Cleaning up ACSF documentation. [\#29](https://github.com/acquia/blt/pull/29) ([grasmash](https://github.com/grasmash))
- Cleaning up example factory hooks. [\#28](https://github.com/acquia/blt/pull/28) ([grasmash](https://github.com/grasmash))
- Refactoring git pre-commit hook to improve performance. [\#27](https://github.com/acquia/blt/pull/27) ([grasmash](https://github.com/grasmash))
- Adds a couple of factory hook examples [\#26](https://github.com/acquia/blt/pull/26) ([kylebrowning](https://github.com/kylebrowning))
- Add directions for overridding the docroot used by Drush for Drupal VM. [\#25](https://github.com/acquia/blt/pull/25) ([geerlingguy](https://github.com/geerlingguy))
- Move Bolt's build status to the top of the README. [\#19](https://github.com/acquia/blt/pull/19) ([geerlingguy](https://github.com/geerlingguy))
- ACSF support for deploy step [\#18](https://github.com/acquia/blt/pull/18) ([damontgomery](https://github.com/damontgomery))
- Make Bolt alias compatible with all Unix environments. [\#15](https://github.com/acquia/blt/pull/15) ([danepowell](https://github.com/danepowell))
- Updating phing after upstream pull request was merged. [\#14](https://github.com/acquia/blt/pull/14) ([grasmash](https://github.com/grasmash))
- Validate composer files. [\#10](https://github.com/acquia/blt/pull/10) ([danepowell](https://github.com/danepowell))
- Fix formatting in local-development.md documentation. [\#7](https://github.com/acquia/blt/pull/7) ([geerlingguy](https://github.com/geerlingguy))
- Removing bolt: prefix from targets. [\#6](https://github.com/acquia/blt/pull/6) ([grasmash](https://github.com/grasmash))
- Removing 80 col line breaks from docs. [\#5](https://github.com/acquia/blt/pull/5) ([grasmash](https://github.com/grasmash))
- Support for aliases [\#2](https://github.com/acquia/blt/pull/2) ([damontgomery](https://github.com/damontgomery))



\* *This Change Log was automatically generated by [github_changelog_generator](https://github.com/skywinder/GitHub-Changelog-Generator)*
