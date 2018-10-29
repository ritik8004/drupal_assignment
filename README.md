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
You typically run all the the drush and blt commands *from inside* of the VM.

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
  * `vagrant ssh` to ssh into your vm
  * `blt refresh:local` (from inside of your vm)
  * Enter the site code you want to setup the site for (this can be avoided by adding the site code in blt params like `blt refresh:local mckw`)
  * Access the site in your web browser, e.g.﻿http://local.alshaya-mckw.com/en/user
  * Login using the default credentials:﻿no-reply@acquia.com / admin
  * Perform drush commands from inside of your vm, like `drush status -l local.alshaya-mckw.com`
  * Login quickly using `drush uli -l local.alshaya-mckw.com`  (note: currently doesn't work properly)
  * Access site through Varnish in local
  * Comment out the code forcing SSL redirection in `docroot/.htaccess`
  * Access the site on port 81
  * To do any change in VCL do it in `conf/varnish-4.vcl`, do `vagrant ssh` and run `sh box/scripts/configure-varnish.sh`

Next builds can be done using: `blt refresh:local:drupal`
Behat tests can be run using: `vagrant ssh --command='cd /var/www/alshaya ; blt tests:behat'`

### Troubleshooting

* `blt refresh:local` failed in drupal installation with EntityStorageException (...) entity with ID '...' already exists
  * The reason for this is in the existing configuration values that still exist in memcache. The workaround is that you either restart the vm using ​vagrant reload​ command, or you restart memcache service using sudo service memcached restart in your vm and restart `blt refresh:local` again
* In case, updates done to default settings.php don't reflect in sites/g/settings/local.settigns.php:
    * `rm -rf docroot/sites/g/settings/local.settings.php` to make sure refresh local or local reset settings updates the file with new settings.
    * `blt local:reset-settings-file` to reset local settings file.

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
  * Add site in `blt/alshaya_local_sites.yml` with proper values (check existing sites for example)
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

You also need to forward your private key to your vm, because all the commands
above need to be run from inside of vm and they reach remote cloud instance.

In order to perform the private key forwarding, do following:

* Edit ​`~/.ssh/config` ​file on your host machine and add following two lines:
  * Host 127.0.0.1
  * ForwardAgent yes
* `​ssh-add ~/.ssh/your_private_key​` (this adds your key to the ssh agent, the path to your private key your connecting to Acquia Cloud with - by default ​~/.ssh/id_rsa​)
* `​ssh-add -L`​ (ensure your private key is listed between the keys displayed on ssh agent) 4. ​vagrant ssh​ (connects to you guest machine)

### Drush aliases

* On your local environment, from inside of the vm, use either `drush -l local.alshaya-<site>.com` or `drush @<site>.local` alias to reach the site. Example: `drush @mckw.local status`
* Acquia cloud sites are reachable from inside of vm (assuming your private key is properly forwarded, see previous paragraph) with `drush @<site>.01<env>` format. For example: `drush @hmkw.01dev3 status` will display status of hmkw site on dev3 environment.
* On Acquia Cloud, the sites are only reachable via drush without the alias from inside of the environment you are in (cross-environment connections are not supported via drush, however a direct ssh/scp connection can be used if needed). Therefore we are not using drush aliases in that case and use format `drush -l <site>.<env>-alshaya.acsitefactory.com` from the application's `docroot` folder to reach that site.

### Optional: Running drush aliases from your host PC

As mentioned above, you should typically run all the blt and drush commands (except the initial one blt vm that initializes your virtual machine) from inside of your vm. This should cover all the typical cases and you typically don’t need to follow this guide if you are fully comfortable with that approach. However, for the better convenience, it is sometimes quicker to run some drush commands from host PC. For example, running drush uli from host logs quickly user 1 into the site without need of copy/pasting of login link. PC To make this working follow these steps:

* Install <a href="https://github.com/drush-ops/drush-launcher">Drush launcher</a> on your host PC (before that, please make sure you don’t need drush 8 for Alshaya anymore, or you archive old drush 8 e.g. under drush8 command - see Transition phase between drush 8 and drush 9 article for more details). Alternatively, you can manually run vendor/drush/drush/drush command if you want to run drush 9 commands without installing Drush launcher
* To connect to vagrant instances from host pc, use @<site>.vm aliases, e.g. `drush @hmkw.vm status`. These aliases cannot be used to sync databases to local (see Technical details on aliases structure for more information)
* To connect to remote sites, use standard `drush @<site>.01<env>` form, e.g. `drush @hmkw.01dev3 status`. Note this will only work if the remote site has already deployed blt9 and drush9 (see next topic)

### Transition phase between drush 8 and drush 9

General rule is: drush 8 site aliases can be properly interpreted only with drush 8, drush 9 sites only with drush 9. That brings some challenges when trying to reach the sites with old (drush 8) codebase from the new (already upgraded) drush 9 local environment.

In order to still reach the old drush 8 sites after upgrade with old drush aliases (e.g. `drush @alshaya.01uat status -l ...`), it’s recommended to make the drush 8 command reachable from host PC (either by keeping the old version on host, or by renaming the drush executable to drush8 or by installing it as a separate distribution) and make it reachable with e.g. drush8 command. That way, you can still reach the drush 8 sites by typing e.g. `drush8 @alshaya.01uat -l ... status`. Ensure the drush8 is reaching the proper version by typing drush8 --version.

### Technical details on drush 9 aliases structure

In Drush 9, we strictly need to differentiate between “local” and “remote” aliases when running some commands (e.g. `drush sql-sync`, `drush browse` etc.), “Local” aliases are identified by “host” and “user” settings in their corresponding *.yml files: these fields must be empty for local aliases and only these aliases can be used e.g. to sync data from cloud environments (drush sql-sync does not support sync between two remote aliases - yet).

Local aliases are the aliases with *.local suffix on Alshaya: e.g. [site].local - they are intended to by only used from inside of vm and are suitable for most common development operations, including sql-sync. On Alshaya, we specify "127.0.0.1" as a host for local aliases for now. It supports some local commands, but it's not entirely correct and prevents currently proper `drush sql-sync` functionality on these aliases. Ideally, host setting should be empty, but currently it doesn't match the particular local sites properly then. The Jira ticket was created to fix that.

Remote aliases are divided into two groups:
* Acquia cloud aliases in form `[site].01[env]`
* Drupal vm aliases in form of `[site].vm` that can be used to reach vm from host PC, but are not usable for any remote sync operations

All site aliases are defined in `drush/sites` folder.

### Local setup of Behat:
* Start Behat installation on your local by following the steps below:
  * Create a directory, say 'alshaya_behat' [if not exist]
  -  * Clone alshaya git repo
  -  * cd alshaya_behat
  -  * composer install
  -  * npm install --prefix bin chromedriver
  -  * (In a separate terminal window) java -Dwebdriver.chrome.driver=bin/node_modules/chromedriver/bin/chromedriver -jar vendor/se/selenium-server-standalone/bin/selenium-server-standalone.jar
  -  * bin/behat features/hmkw/manual/basket.feature --profile=(hmuat,hmqa,mckwqa,mckwuat)
    
### How to interpret the Behat reports:
  * When the execution of the feature file is completed, navigate to build directory which is inside your parent directory
  * Open html->behat->index.html. This has your test execution details for the last run only. This gets overwritten with new execution.
  * In order to share the reports, compress the html directory immediately after every run.
