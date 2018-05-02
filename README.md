# Alshaya

ACSF D8 commerce project with Magento integration done via Acquia Conductor.
Multi brands and multi countries solution for selling Alshaya products online.

## Build status for main branches:
* Develop: <img src="https://api.travis-ci.com/acquia-pso/alshaya.svg?token=4JaqUUFo3VuBYCWd867z&branch=develop" alt="develop build status" />
* Test (QA): <img src="https://api.travis-ci.com/acquia-pso/alshaya.svg?token=4JaqUUFo3VuBYCWd867z&branch=qa" alt="qa build status" />
* UAT: <img src="https://api.travis-ci.com/acquia-pso/alshaya.svg?token=4JaqUUFo3VuBYCWd867z&branch=uat" alt="uat build status" />
* Master: <img src="https://api.travis-ci.com/acquia-pso/alshaya.svg?token=4JaqUUFo3VuBYCWd867z&branch=master" alt="master build status" />

## BLT

See the [BLT documentation](http://blt.readthedocs.io/en/latest/) for information on build, testing, and deployment processes.

## Resources

* <a href="http://jira.alshaya.com:8080/secure/RapidBoard.jspa?rapidView=284">JIRA</a> - VPN Connection required.
* <a href="https://github.com/acquia-pso/alshaya">GitHub</a>
* <a href="https://cloud.acquia.com/app/develop/applications/5ce588f5-ce9b-4a46-9b5b-a0c74e47feb2">Acquia Cloud subscription</a>
* <a href="https://travis-ci.com/acquia-pso/alshaya">TravisCI</a>

## Onboarding

### Development workflow

There are 2 git repositories on the project:
* Github: used for local development, contains much more than what is needed
to run the sites.
* Acquia Cloud: used on Acquia environments only, contains the strict minimum
to run the sites.

The synchronisation between the 2 repositories is ensured by blt.
Cf. `blt deploy`.

### Local git repository

Each developer uses a fork of the main repository for his developments and
creates Pull Requests (PRs) against the main repository when he is ready.

To prepare your local env:
* Fork the main repository into your own repository, i.e. click on Fork button
on https://github.com/acquia-pso/alshaya page.
* Clone the fork locally: `git clone git@github.com:<your_username>/alshaya.git`
* Add a git remote for the main repository (aka upstream):
  * `cd alshaya`
  * `git remote add upstream git@github.com:acquia-pso/alshaya.git`
  * `git remote set-url --push upstream DISABLED`

From now on, you will fetch/pull from upstream remote to get the changes that
have been merged into the main repository and push to origin remote to save
changes and create PRs.

### Local dev environment

The team uses Drupal VM. It provides a vagrant box that has been customized to
fit the needs of the project.
The principle is that the configuration of the VM is stored in the git
repository so that each developer uses the same configuration which is as close
as possible to the configuration of the Prod env.

To prepare your local env:
* Install Virtualbox and Vagrant.
  * `vagrant plugin install vagrant-vbguest`
  * `vagrant plugin install vagrant-hostsupdater`
* Install Yarn `npm i -g yarn`.
* Install Ansible: `brew install ansible`
* Run:
  * `composer clear-cache`
  * `composer install`
  * `composer blt-alias`
  * `blt vm`
  * `blt refresh:local`
  * Enter the site code you want to setup the site for (this can be avoided by adding the site code in blt params like `blt refresh:local mckw`)
  * `drush @alshaya.local uli`
* Access site through Varnish in local
  * Comment out the code forcing SSL redirection in `docroot/.htaccess`
  * Access the site on port 81
  * To do any change in VCL do it in `conf/varnish-4.vcl`, do `vagrant ssh` and run `sh box/scripts/configure-varnish.sh`

Next builds can be done using: `blt refresh:local:drupal`
Behat tests can be run using: `vagrant ssh --command='cd /var/www/alshaya ; blt tests:behat'`

### Create a new site

Currently, 2 distinct installation profiles exist in the code base. `transac`
installation profile will enable all the modules required for an a site with
commercial features and integration with Conductor/Magento. `non_transac`
profile is a light/static site without any commercial feature.`

* Create a new theme for your site. See `docroot/themes/custom/README.md` for
more information on how to create a custom theme.
* Create a custom module in `docroot/modules/brands`. This module goal is to
enable the appropriate theme, place the blocks in the theme's regions and
install the specific configuration. See existing brand modules for example.
* Add a new brand support:
  * Add DB and Alias in `box/config.yml`
  * Add site in `blt/project.local.yml` with proper values (check existing sites for example)
  * (For transact site) Add proper settings for the new site in 
    * factory-hooks/environments/magento.php
    * factory-hooks/environments/settings.php
    * factory-hooks/environments/conductor.php
* `vagrant reload --provision`
* Run `blt refresh:local` commands and enter appropriate site code when asked.

### Update local site from cloud.
Script is created to download db from cloud env and configure local env
with that. We also enable stage_file_proxy to ensure files are available
in local as and when required. All required changes are done.
* Configure stage_file_proxy
* Update super admin user
* Enable dblog and other ui modules
* Allows hooking into the script, we can create scripts/install-site-dev.sh
which is already added to .gitignore and add any code we want to execute post
this script (for instance command to shout loud in mac - `say installation 
done`). One argument - site code will be passed to this script.

Script usage:
* `blt local:sync "site" "env" "mode"`
* `blt local:sync mckw dev reuse`
* `blt local:sync mckw dev download`
* `blt local:download "site" "env"`

Be careful in using the mode download, it will take time as it does sql-dump
using drush which can take too much of time.

### Local setup of Behat:
* Start Behat installation on your local by following the steps below:
  * Create a directory, say 'alshaya_behat' [if not exist]
  * cd into the above directory - `cd alshaya_behat`
  * Create a file composer.json and paste the contents below in it: [if not exist]
  
    `{
    "require-dev" : {
    "behat/behat" : "3.0.*",
    "behat/mink-goutte-driver" : "*",
    "behat/mink-browserkit-driver" : "*",
    "behat/mink-extension" : "2.*",
    "behat/mink-selenium2-driver" : "*",
    "behat/mink" : "*",
    "drupal/drupal-extension" : "~3.0",
    "drupal/drupal-driver": "1.1.*",
    "emuse/behat-html-formatter": "^0.1.0"
    },
  "config": {
  "bin-dir": "bin/"  
  }
}`
* Save the file and close it
* If you don't have composer installed, Install composer by running the commands:
  * `curl -sS https://getcomposer.org/installer | php`
  * `php composer.phar install`
* If you already have composer installed, then run the command as below:
  `composer install`
* Create behat.yml file
* Paste the content below in the yml file:

  `#behat.yml
  default:
    autoload:
      '': %paths.base%/features/bootstrap
    suites:
      default:
        contexts:
          - FeatureContext
          - Drupal\DrupalExtension\Context\MinkContext
          - Drupal\DrupalExtension\Context\MessageContext
          - Drupal\DrupalExtension\Context\DrupalContext 
        paths:
          - %paths.base%/features
    formatters:
      html:
        output_path: %paths.base%/build/html/behat      
    extensions:
      Behat\MinkExtension:
          browser_name: 'chrome'
          goutte:
            guzzle_parameters:
              verify: false
          javascript_session: selenium2
          selenium2:
            wd_host: http://127.0.0.1:4444/wd/hub
            capabilities: { "browser": "chrome", "version": "59.0.3071.115", 'chrome': {'switches':['--start-maximized']}}
          base_url: 'https://whitelabel2.test-alshaya.acsitefactory.com/'
          files_path: "%paths.base%/files"
      Drupal\DrupalExtension:
        blackbox: ~
      emuse\BehatHTMLFormatter\BehatHTMLFormatterExtension:
          name: html
          renderer: Twig,Behat2
          file_name: index
          print_args: true
          print_outp: true
          loop_break: true`
          
* Initialize Behat by running the command
  `bin/behat --init`
* Features and bootstrap directory should get created after initializing Behat
* Download all the feature files from Alshaya git repository and place it under 'features' directory on your local Behat setup
* Download the FeatureContext.php from Alshaya git repository and replace it with the local copy. You should be able locate the file under features -> bootstrap -> FeatureContext.php
* Follow the steps below to change the environment on which you want to run tests on:
  * Open behat.yml file
  * Set parameter 'base_url' to the instance you want to run tests on
  * Save the file
* Install JDK on your machine.
* Download the latest Selenium standalone server from http://www.seleniumhq.org/download/
* Download the latest chrome driver from https://sites.google.com/a/chromium.org/chromedriver/downloads
* Install npm
  - `npm install --prefix bin chromedriver`
* Run the selenium server on your machine by using the following command:
 - (In a separate terminal window) `java -Dwebdriver.chrome.driver=bin/node_modules/chromedriver/bin/chromedriver -jar vendor/se/selenium-server-standalone/bin/selenium-server-standalone.jar`
* You are now good to start runnning Behat scripts on your machine
* Below are various ways to run Behat feature files:
  * To run a single feature file with specific brand - `bin/behat features/<brand>/manual/<feature> --profile=<instance>` [<brand> i.e. hmkw, <feature> i.e. signin.feature, <instance> i.e. hmuat]
  * To run a single feature file with multi brand - `bin/behat features/hmkw/manual/basket.feature --profile=(hmuat,hmqa,mckwqa,mckwuat)`
  * To run all the feature files - `bin/behat features`
  * To run a single feature file - `bin/behat features/filename.feature` (e.g. `bin/behat features/checkout.feature`)
  * To run tagged scenarios - `bin/behat features --tags @tagname` (e.g. `bin/behat features --tags @checkout`)
  
### Some pre-requisites to run Behat tests
  * Make sure the products mentioned in the feature file is available on the instance you wish you to execute tests. For e.g. if you wish to run 'checkout.feature' on your local, perform the steps mentioned below:
  * Open FeatureContext.php file
  * Search for method "iAmOnASimpleProductPage"
  * In the visitPath function, pass the value of the URL for a simple product
  * In the method "iShouldSeeTheLinkForSimpleProduct", pass the href link for the simple product which can be got from its PLP
  * Perform similar steps for the configurable product too
  * Methods that need to be modified similarly are as below:
    * iAmOnASimpleProductPage
    * iAmOnAConfigurableProduct
    * iShouldSeeTheLinkForSimpleProduct
    * iShouldSeeTheLinkForConfigurableProduct
    * iShouldNotSeeTheLinkForSimpleProduct
    * iShouldNotSeeTheLinkForConfigurableProduct
    * iShouldSeeTheLinkForSimpleProductInArabic
    * iShouldSeeTheLinkForConfigurableProductInArabic
    
### How to interpret the Behat reports:
  * When the execution of the feature file is completed, navigate to build directory which is inside your parent directory
  * Open html->behat->index.html. This has your test execution details for the last run only. This gets overwritten with new execution.
  * In order to share the reports, compress the html directory immediately after every run.
