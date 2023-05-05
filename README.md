# Alshaya

ACSF commerce project with Magento integration done via Acquia Conductor (ACM).
Multi brands and multi countries solution for selling Alshaya products online.

## Build status for main branches:
* Develop: [![Develop](https://github.com/acquia-pso/alshaya/actions/workflows/deploy.yml/badge.svg?branch=develop)](https://github.com/acquia-pso/alshaya/actions/workflows/deploy.yml)
* Test (QA): [![QA](https://github.com/acquia-pso/alshaya/actions/workflows/deploy.yml/badge.svg?branch=qa)](https://github.com/acquia-pso/alshaya/actions/workflows/deploy.yml)
* UAT: [![UAT](https://github.com/acquia-pso/alshaya/actions/workflows/deploy.yml/badge.svg?branch=uat)](https://github.com/acquia-pso/alshaya/actions/workflows/deploy.yml)

## BLT

See the [BLT documentation](http://blt.readthedocs.io/en/latest/) for information on build, testing, and deployment processes.

## Resources

* [JIRA](https://alshayagroup.atlassian.net/secure/RapidBoard.jspa?rapidView=353)
* [GitHub](https://github.com/acquia-pso/alshaya)
* [Acquia Cloud subscription](https://cloud.acquia.com/app/develop/applications/5ce588f5-ce9b-4a46-9b5b-a0c74e47feb2)
* [CI](https://github.com/acquia-pso/alshaya/actions)

## Onboarding

### Development workflow

There are 2 git repositories on the project:
* Github: used for local development, contains much more than what is needed
  to run the sites.
* Acquia Cloud: used on Acquia environments only, contains the strict minimum
  to run the sites.

The synchronisation between the 2 repositories is ensured by blt.
Cf. `blt deploy`.

#### Back merges

Any code merged into upstream branch is automatically back-merged.

* There is Heroku APP running to do this
* Back merges are done from master => uat => qa => develop => develop-*

### Local git repository

Each developer uses a fork of the main repository for their developments and
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

We have a **ddev** setup available.
To setup lando, follow the instructions mentioned here https://github.com/acquia-pso/alshaya/blob/develop/README.ddev.md.

### React Modules.

#### Build React files for local.
if someone wants to just rebuild the files to use in local `blt react-build` can be used.
For

#### React module development.
Go through the [README file](./docroot/modules/react/README.md) to start with react module development.

### Running behat tests with headless Chrome locally on MacOS
This reproduces the travis behavior closely (travis is running selected tests from alshaya_behat folder on daily basis), so use this way if your tests behave differently from travis.
You **do not** need Java/Selenium/BLT installed for this.

* Make sure you have Google Chrome browser installed on your host PC
* Navigate to your repo root (you will run all commands below from repo root of host PC)
* Make alias to your chrome browser, e.g. `alias chrome="/Applications/Google\ Chrome.app/Contents/MacOS/Google\ Chrome"`
* Run `chrome --headless --remote-debugging-port=9222 --window-size=1920,1080 http://localhost`
* Open new terminal window
* Run `vendor/bin/behat  --format pretty alshaya_behat/features/hmkw/common/sign_in.feature --colors --no-interaction --stop-on-failure --strict --config tests/behat/integration.yml --profile hmkw-uat -v`

Notes:
* Replace the "sign_in.feature" with other features or folder to run different tests
* Replace "hmkw-uat" profile with other profiles, see `tests/behat/integration.yml` file for more examples.
* While headless chrome is running, you can visit http://localhost:9222 to see how the "screen" of the tests looks like.
* You can also see the screenshots of failed tests in `tests/behat/reports` folder

### Troubleshooting

* `blt refresh:local` failed in drupal installation with EntityStorageException (...) entity with ID '...' already exists
  * The reason for this is in the existing configuration values that still exist in memcache. The workaround is that you either restart lando or memcache.
* In case, updates done to default settings.php don't reflect in sites/g/settings/local.settigns.php:
  * `rm -rf docroot/sites/g/settings/local.settings.php` to make sure refresh local or local reset settings updates the file with new settings.
  * `blt local:reset-settings-file` to reset local settings file.

#### Merging Update Hooks

We use update hooks to manage all the configuration changes and most of the manual steps. If there is feedback on an update hook and we need to modify the code inside it is difficult to re-run the hook again on the non-prod environment where it is already executed.

To achieve this we have created a utility drush command `reset-update-hook`.

Whenever required to run an update hook again, simply run below from your local machine:
`blt cloud:drush [ENV] reset-update-hook [MODULE] [UPDATE HOOK VERSION NUMBER]`

Example: `blt cloud:drush dev reset-update-hook alshaya_master 9403`

Post execution of above command, run drush updb as usual and check for the messages. 

### Create a new site

Currently, 2 distinct installation profiles exist in the code base. `transac`
installation profile will enable all the modules required for an a site with
commercial features and integration with Conductor/Magento. `non_transac`
profile is a light/static site without any commercial feature.`

* Create a custom module in `docroot/modules/brands`. This module's goal is to
  enable the appropriate theme, place the blocks in the theme's regions and
  install the specific configuration. See existing brand modules for example.
* Install the new theme for the respective brand inside this brand module.
* See "Create a new theme for the site." for specific instructions on creating a new theme.
* Add a new brand support:
  * Add site in `blt/alshaya_sites.yml` with proper values (check existing sites for example)
  * Add drush aliases to the site into `drush/sites` folder with proper values (check existing sites for example)
  * (For transact site) Add proper settings for the new site in
    * factory-hooks/environments/magento.php
    * factory-hooks/environments/settings.php
    * factory-hooks/environments/conductor.php
* `vagrant reload --provision`
* Run `blt refresh:local` commands and enter appropriate site code when asked.

### Create a new theme for the site.

#### Overview.
* There are three three different theme types, transac theme, non transac theme and amp theme.
* You will find this under `themes/custom/transac`, `themes/custom/non_transac` and `themes/custom/amp`.
* We heavily rely on theme inheritance, so each theme type has a alshaya specific base theme to spped up the theme creation process.
* Transac theme is for trnasactional sites, and `alshaya_white_label` is used as a base theme by default for transac installation profile.
* Non-Transac theme is for non transactional sites, which are usually a placeholder sites until the transactional sites are launched. `whitelabel` is used as a base theme by default for non transac installation profile.
* AMP themes use "Accelerated Mobile Pages" specifications for certain pages on the site, AMP theme is by default enabled for all profiles. `alshaya_amp_white_label` is the base AMP theme.
* Also see `docroot/themes/custom/README.md` for some additional steps related to CI applicable to all themes.

#### Transac theme.
* `alshaya_example_subtheme` is designed as a copy paste starter kit for new brands.
* Check `docroot/themes/custom/transac/alshaya_example_subtheme/README.md` for steps to create a new transac theme.
* Use standard Drupal practices to override anything that is coming from base theme.
* Use any of the existing brand themes as an example for reference.

#### Non Transac theme.
* Duplicate any of the brand themes inside `non_transac` directory.
* Change the info file, directory name and hook names in approprite files.
* Non transac themes use patternlab with all the components inherited from base theme.
* Remove any scss files inside sass directory from the duplicated themed.
* You should have a clean non transac theme with all styles inherited from base theme.
* Override what is necessary.

#### AMP theme.
* AMP as a spec provides limited customization.
* If any custom work is needed for a brand on AMP pages, copy paste any of the brand amp themes.
* Change the name of the theme in appropriate files.
* Override what is necessary, follow other brand AMP themes as reference.

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
* `blt local:sync hmkw2 uat reuse`
* `blt local:sync vsae3 dev2 download`
* `blt local:download "site" "env"`

Be careful in using the mode download, it will take time as it does sql-dump
using drush which can take too much of time.

You also need to forward your private key to lando, because all the commands
above need to be run from inside of lando and they reach remote cloud instance.

In order to perform the private key forwarding, do following:

* Edit `~/.ssh/config` file on your host machine and add following two lines:
  * Host 127.0.0.1
  * ForwardAgent yes
* `ssh-add ~/.ssh/your_private_key` (this adds your key to the ssh agent, the path to your private key your connecting to Acquia Cloud with - by default ~/.ssh/id_rsa)
* `ssh-add -L` (ensure your private key is listed between the keys displayed on ssh agent) 4. vagrant ssh (connects to you guest machine)

### Drush aliases

* On your local environment, use either `lando drush -l <site>.alshaya.lndo.site` or `lando drush @<site>.local` alias to reach the site. Example: `lando drush @mckw.local status`
* Acquia cloud sites are reachable (assuming your private key is properly forwarded, see previous paragraph) with `lando drush @<site>.01<env>` format. For example: `lando drush @hmkw.01dev3 status` will display status of hmkw site on dev3 environment.
* On Acquia Cloud, the sites are only reachable via drush without the alias from inside of the environment you are in (cross-environment connections are not supported via drush, however a direct ssh/scp connection can be used if needed). Therefore we are not using drush aliases in that case and use format `lando drush -l <site>.<env>-alshaya.acsitefactory.com` from the application's `docroot` folder to reach that site.

### Optional: Running drush aliases from your host PC

It is sometimes quicker to run some drush commands from host PC. For example, running drush uli from host logs quickly user 1 into the site without need of copy/pasting of login link. PC To make this working follow these steps:

* Install [Drush launcher](https://github.com/drush-ops/drush-launcher) on your host PC (archive old drush8 command somewhere if you want to run it for another projects)
* To connect to remote sites, use standard `drush @<site>.01<env>` form, e.g. `drush @hmkw.01dev3 status`. Note this will only work if the remote site has already deployed blt9 and drush9 (see next topic)

### Technical details on drush 9 aliases structure

In Drush 9, we strictly need to differentiate between “local” and “remote” aliases when running some commands (e.g. `drush sql-sync`, `drush browse` etc.), “Local” aliases are identified by “host” and “user” settings in their corresponding *.yml files: these fields must be empty for local aliases and only these aliases can be used e.g. to sync data from cloud environments (drush sql-sync does not support sync between two remote aliases - yet).

Remote aliases are divided into two groups:
* Acquia cloud aliases in form `[site].01[env]`

All site aliases are defined in `drush/sites` folder.

### Local setup of Behat:
* Start Behat installation on your local by following the steps below:
  * Create a directory, say 'alshaya_behat' [if not exist]
  -  * Clone alshaya git repo
  -  * cd alshaya_behat
  -  * composer install
  -  * npm install --prefix bin chromedriver

### Behat execution Process

### Prerequisites for behat.yml before running the scripts
* Ensure the base url is correct for the profile you are using.
* Ensure the config product url is valid and is in stock.

Scripts location:
QA/UAT: sitename/common folder has all scripts that can be executed on QA/UAT envs.
Production: Scripts starting from Sanity_** are the only ones that should be executed on production.

Execution:

* Navigate to alshaya_behat folder in terminal window
* Initialise selenium by executing below command
* (In a separate terminal window) java -Dwebdriver.chrome.driver=bin/node_modules/chromedriver/bin/chromedriver -jar vendor/se/selenium-server-standalone/bin/selenium-server-standalone.jar
* (In a separate terminal window) navigate to alshaya_behat folder
* bin/behat features/mothercare/common/checkout.feature --profile=mcuat
* The above command will run the checkout.feature script for MC and the profile used should be according to environment.
* Another way to run: Use tags e.g
  bin/behat --@tagname --profile=mcuat



### How to interpret the Behat reports:
* When the execution of the feature file is completed, navigate to site folder directory which is inside your parent directory. e.g (hmkw)
* Open html->behat->index.html. This has your test execution details for the last run only. This gets overwritten with new execution.
* In order to share the reports, compress the html directory immediately after every run.

### Test Results analysis:
* If any scenarios fail, try re-running the script once to discard any network issues.
* If they still fail, check the scenarios if they are failing due to data issue (some text assertions changed on the site)
* Check by running the scenario manually if it passes
* Check by running the scenario individually to see if it passes (use tags)

### Debugging with xdebug

A recommended IDE for debugging is PhpStorm. However, if you use another IDE, you should be able to apply the guidelines below with some tweaks.

> *NOTE: XDEBUG Port configured in lando is 9003*

#### Browser-based debugging

XDebug debugger is enabled by default. In order to debug your code from browser, do following:
* In Google Chrome browser, install “Xdebug helper” extension
* In PhpStorm, select from menu “Run/Start listening for PHP debug connections”
* Set breakpoint somewhere in the code
* Refresh the page
* Debugger should stop at the breakpoint you set

#### CLI Debugging

**CLI debugging is disabled by default.** The reason is significant performance degradation of all php scripts (incl. composer, drush, blt...) when xdebug is enabled. However, CLI debugging is important, especially for debugging behat tests or unit tests. To enable CLI debugging, do following:

* Make sure you firstly invoked debugger from browser at least once.
* In File/Preferences of phpStorm, section Languages & Frameworks / PHP / Servers, write down server name the debugger invoked in previous step (e.g. "mckw.alshaya.lndo.site")
* Run `lando xdebug-on` to start xdebug.
* After debugging is over, run `lando xdebug-off` to stop xdebug.

Specific notes for debugging drush commands:
* Use full path to drush in vendor folder (vendor/drush/drush/drush) instead of drush command itself (it runs launcher which is typically outside of codebase)
* You will eventually need to fix path mappings for the commands like drush and point them to your Alshaya codebase in vendor folder
* Do NOT use drush aliases like @mckw.local for debugging, use always the -l parameter instead (see above for a valid example)

After finishing CLI debuging it's recommended to disable xdebug back again, to increase performance. To debug CLI commands like Drush, Drupal console or PhpUnit, follow these steps:

### Remote debugging from ACSF
On ACSF dev, dev2 and dev3 environments, xdebug is enabled for remote debugging.
Follow instructions [here](https://support.acquia.com/hc/en-us/articles/360006231933-How-to-debug-an-Acquia-Cloud-environment-using-PhpStorm-and-Remote-Xdebug) to set up remote debugging on your local PhpStorm to leverage it.

### XHPROF in local
#### Setup
Download and add [xhprof](https://www.drupal.org/project/xhprof) in docroot/modules/development

#### Usage
* Enable the module (if not enabled already)
* Add profile=1 in query string to any URL which you want to profile
* Check [factory-hooks/post-settings-php/xhprof.php](factory-hooks/post-settings-php/xhprof.php) for more details on default configuration

### Enable Apple-Pay on local.
* Setup Apple pay wallet (https://alshayagroup.atlassian.net/wiki/spaces/ACSF/pages/577208482/Apple+Pay+-+Setup)
* Download / Copy SSL (merchant_id.key and merchant_id.pem files) from Cloud dev/test environment of any brand and place them in local environment folder as per paths (usually /var/www/apple-pay-resources) defined in factory-hooks/pre-settings-php/apple_pay.php
* The Apple-Pay payment method appears on desktop view if "apple_pay_allowed_in" key from configuration acq_checkoutcom.settings is set to 'all'
  Drush command to set config to all: drush -l <site-url> cset acq_checkoutcom.settings apple_pay_allowed_in 'all' --input-format=yaml

### Finding deprecated code (using drupal-check)
* Command format: drupal-check [OPTIONS] [DIRS]

* Options:
    -a Check analysis
    -d Check deprecations (default)
    -e Exclude directories. Wildcards work. Separate multiple excluded directories with commas, no spaces. e.g.: */tests/codeception/acceptance/*.php
    --drupal-root Path to Drupal root. Fallback option if drupal-check could not identify Drupal root from the provided path(s).

* Deprecations.
  - vendor/mglaman/drupal-check/drupal-check -d docroot/modules/custom
* Analysis.
  - vendor/mglaman/drupal-check/drupal-check -a docroot/modules/custom

### General Notes
#### Issue with accessing cloud urls from Linux Systems
There is an issue observered when trying to access dev/uat etc website urls from linux systems.
The issue happens because CloudFlare blocks the request and we see the message `Site can't be reached` in the browser.
In such a situation, we need to add the following  mapping to the `/etc/hosts` file in the local system and then access the website again (the IP address can be the same for all the domains)
```
104.16.65.106	hmkw5-pprod.factory.alshaya.com
104.16.65.106	weae2-test.factory.alshaya.com
```

#### Cloudflare Worker for Pausing ACM Queues

We observed that combination of high traffic and ACM pushes (push for sync from MDC to Drupal) slows down the sites heavily. This leads to increased pushes from ACM as it considers request as failed if the response time is greater than 2 seconds.

To solve this we came up with 3 part solution:
* Secret information in Cloudflare KV NameSpace AlshayaAcquiaStability
  * To access or update the data utility scripts are available [here](tests/apis/cloudflare)
* Cloudflare Worker JavaScript Code available [here](architecture/cloudflare/alshaya-acquia)
  * Code here accesses the ACM secret information based on the parameters sent via request headers by New Relic
  * It pauses the ACM queues for all the Site IDs available for particular stack as per the data available in secrets
  * It sends a message to [#server-loaded](https://app.slack.com/client/T4ARH3F8D/CQNGAKD16) Slack channel.
* New Relic Webhook
  * We invoke the panic-on URL from New Relic whenever average response time in last 5 minutes is > XX seconds (1.5 for Stack 5)
  * This Webhook is invoked in both cases today (when the incident is triggerred and when it is resolved)

It is then BAU team responsibility to check if queues can be resumed using [script](tests/apis/conductor_v2/pauseQueues.php) or must be processed individually before resuming [script](tests/apis/conductor_v2/processQueues.php).

[Check this recording to understand more about the same](https://acquiamagentoal.slack.com/files/UDEB422D7/F04EC8YGCET/alshaya_-_devops.mp4)
