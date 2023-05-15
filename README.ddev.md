# Running with DDEV for local development

It is possible to work with DDEV for local development.

#### Pending items:
* Make SOLR work (we still use for non-transac)

### Switching to DDEV
* Reset your [local.settings.php](docroot/sites/g/settings/local.settings.php) file
  from [default.local.settings.php](docroot/sites/default/settings/default.local.settings.php)
* Shutdown Lando
* Restart docker

### Requirements

* Docker
* DDEV
* MacOS or Ubuntu/Linux or Ubuntu on Windows WSL2

### Installation
* Follow installation steps from [here](https://ddev.readthedocs.io/en/latest/users/install/ddev-installation/)

All steps are executed on your host OS.

  * `ddev start` - this will configure and set up your containers and services.
  * `ddev composer install` - This will install all the composer packages.
  * `ddev composer build-middlewares` - This will install all the composer packages for the middleware applications.
  * `ddev create-databases` - This will create all the required databases.
  * `ddev blt blt:init:git-hooks` - this will initialize git hooks.
  * `ddev blt blt:init:settings` - this will initialize settings.
  * `ddev blt frontend:setup` - see notes on BLT & NPM below
  * `ddev blt frontend:build` - see notes on BLT & NPM below
  * `ddev blt refresh:local <sitename>` - where <sitename> is the site you want to build. If you don't specify the
     site name, you will be able to pick the name from a list.
     * Please run `ddev auth ssh` to make sure your SSH key is available inside DDEV.

You should now be able to access the site in your browser at `https://<sitename>.alshaya.lndo.site/`
example: `https://mckw.alshaya.lndo.site/`

Drush commands can be executed from your host OS using `ddev drush -l <site_url>`.

### Post Destroy / Starting fresh

After every-time `ddev delete` is done we need to do following post `ddev start`

* `ddev create-databases` - This will create all the required databases.


### MySQL

If you're adding new sites
* Add the new site as described in [README file](./README.md#create-a-new-site)
* No need to rebuild like in vagrant
* After adding entry in blt/alshaya_sites.yml just run `ddev create-databases`

### Varnish

Coming soon

### Memcache

We are using one memcache service the same way as what we had in DrupalVM / Vagrant setup.

### Mailhog

To read the mails sent by system please execute `ddev launch -m`

## PHPMyAdmin

To access database via PHPMyAdmin please execute `ddev launch -p`

## Tooling

### Logs

To access Drupal Logs we can use `ddev ssh; tail -f /var/log/syslog /var/log/apache2/*.log`

### BLT

We've provided BLT tooling so that you can run BLT commands inside the container using `ddev blt <command>`.

### NPM and NODE components setup.

To execute any NPM commands with ddev, use `ddev npm run build` or `ddev npm run build:dev`.
Theme and React compilation works same way, execute the commands from inside the module/theme folder.

For example to compile the JS for `alshaya_spc`.
```
$ cd docroot/modules/react/alshaya_spc
$ ddev npm run build:dev
```

keep global node version 14.x+ in host machine to aviod react linting issue.

### Xdebug

Xdebug can be enabled/disabled quickly by using the `ddev xdebug on` and `ddev xdebug off` commands.

When xdebug is enabled, it will also apply to drush commands executed with `ddev drush`.

### Xhprof

Xhprof is out of the box available from DDEV, documentation [here](https://ddev.readthedocs.io/en/latest/users/debugging-profiling/xhprof-profiling/)

## Performance

No known performance issues as of now for DDEV.

## Text Editors
### VS Code
1. In the XDebug configuration, make sure you have the following
```
"port": 9003,
"pathMappings": {
  "/var/www/html/": "${workspaceFolder}"
},
```

## Notes

### Captcha
Separate Captcha keys are configured for local, they will work only for
KW, SA and AE markets (restrictions are there for 50 sites so only some
are configured).

To register for any site where captcha doesn't work (and you are not looking at
captcha specific issues), please disable it by adding following code in your
local settings file.

```php
$config['captcha.captcha_point.user_register_form']['status'] = FALSE;
```

### FB Login
FB Login will work only on following domains. Please use them for any
investigation.

* aeokw.alshaya.lndo.site
* bbwkw.alshaya.lndo.site
* bpkw.alshaya.lndo.site
* flkw.alshaya.lndo.site
* hmkw.alshaya.lndo.site
* mckw.alshaya.lndo.site
* mukw.alshaya.lndo.site
* pbkw.alshaya.lndo.site
* pbkkw.alshaya.lndo.site
* vskw.alshaya.lndo.site
* wekw.alshaya.lndo.site
* tbskw.alshaya.lndo.site
* coskw.alshaya.lndo.site
* dhkw.alshaya.lndo.site

### Google Login
Google login is expected to work in local for all the sites.
