# Running with Lando for local development

It is possible to work with Lando for local development.

Using Lando should be considered in BETA phase.

#### Pending items:
* XHPROF
* Make SOLR work (we still use for non-transac)

### Switching from DrupalVM to Lando
* Reset your local.settings.php (docroot/sites/g/settings/local.settings.php) file
  from docroot/sites/default/settings/default.local.settings.php
* Shutdown (no need to destroy) vagrant (vagrant halt or vagrant suspend)

### Build steps

If you are switching from DrupalVM to Lando, please check the steps below.

### Requirements

* Docker
* Lando
* MacOS or Ubuntu/Linux (_*Not tested on Windows_)

### Installation
* Follow https://docs.lando.dev/basics/installation.html
_**NOTE**: Lando recommends you download their installer which has docker desktop bundled with it instead of installing
separately._
* Download the dmg file from `https://github.com/lando/lando/releases`
* The lando package will have the compatible docker as well inside it.
* Post installation, follow the recommendations in the [Performance](#Performance) section below.

Add the below line in ~/.bashrc.

  * Linux user: add 'export LANDO_SSH_AUTH_SOCK="${SSH_AUTH_SOCK}"' at the end of your ~/.bashrc:
  * Mac user: MacOS specific path is here as the variable default value, nothing to do.

All steps are executed on your host OS.

  * `npm i -g yarn` - Install Yarn (make sure npm is available before this is executed).
  * `lando start` - this will configure and set up your containers and services.
  * `lando composer install` - This will install all the composer packages.
  * `lando composer build-middlewares` - This will install all the composer packages for the middleware applications.
  * `lando create-databases` - This will create all the required databases.
  * `lando blt blt:init:git-hooks` - this will initialize git hooks.
  * `lando blt blt:init:settings` - this will initialize settings.
  * `lando blt frontend:setup` - see notes on BLT & NPM below
  * `lando blt frontend:build` - see notes on BLT & NPM below
  * `lando blt refresh:local <sitename>` - where <sitename> is the site you want to build. If you don't specify the
     site name, you will be able to pick the name from a list.

You should now be able to access the site in your browser at `https://<sitename>.alshaya.lndo.site/`
example: `https://mckw.alshaya.lndo.site/`

Drush commands can be executed from your host OS using `lando drush -l <site_url>`.

### Post Destroy / Starting fresh

After every-time `lando destroy` is done we need to do following post `lando start`

* `lando create-databases` - This will create all the required databases.

## Services

The following ports are exposed on localhost

 - 33061 : mysql

Having mysql exposed on localhost is useful for connecting to mysql from clients running on the host OS, such as
"Sequel Pro".

### MySQL

If you're adding new sites
* Add the new site as described in [README file](./README.md#create-a-new-site)
* No need to rebuild like in vagrant
* After adding entry in blt/alshaya_local_sites.yml just run `lando create-databases`

### Varnish

There is a separate varnish file for Lando in `architecture/varnish/varnish-4-lando.vcl`. The existing file did not
compile when using Lando, hence the need for a Lando specific version.

THIS IS NOT TESTED YET

### Memcache

We are using one memcache service the same way as what we had in DrupalVM / Vagrant setup.

### Mailhog

To read the mails sent by system please access http://mail-alshaya.lndo.site

## PHPMyAdmin

To access database via PHPMyAdmin, please access http://pma-alshaya.lndo.site

## Tooling

### Logs

To access Drupal Logs we can use `lando logs-drupal`

### BLT

We've provided BLT tooling so that you can run BLT commands inside the container using `lando blt <command>`.

However, in the case of the frontend build tools, these are executed within a node container. Therefore, we're not
actually executing the BLT commands in this case - we're doing what the BLT commands would ultimately do, which is
running some scripts from our `blt/scripts` folder.

This is not ideal since it means we could get out of date if the blt code in that area changes.

### NPM and NODE components setup.

To execute any NPM commands with lando, use `lando npm run build` or `lando npm run build:dev`.
Theme and React compilation works same way, execute the commands from inside the module/theme folder.

For example to compile the JS for `alshaya_spc`.
```
$ cd docroot/modules/react/alshaya_spc
$ lando npm run build:dev
```

keep global node version 14.x+ in host machine to aviod react linting issue.

### Xdebug

Xdebug can be enabled/disabled quickly by using the `lando xdebug-on` and `lando xdebug-off` commands.

When xdebug is enabled, it will also apply to drush commands executed with `lando drush`.

### Behat

Lando can be used to run behat tests against environments via a local selenium container. Custom build and run
tooling is provided for this purpose.

See 'Testing' section.

## Testing

A chrome selenium container runs as the "selenium" service. It is possible to easily build, run and optionally
observe the tests using custom tooling.

The selenium services exposes port 4444 for VNC connections so that the tests can be observed running in a browser
if desired. See the "observing behat" section.

### Building Behat Profiles

To build the behat test profiles, run the `lando behat-build` command.

This will create various profiles in the `build/profiles` folder. You will need to know this when running the tests.

### Running Behat Tests

To run the tests, you should first choose a profile that was generated into the `builds/profiles` folder.

Then use `lando behat-run --profile=<profile_name>`

For example, `lando behat-run --profile=hm-kw-uat-en-desktop`.

### Observing Behat

On the latest MacOS you can use **Screen Sharing** app to connect using `localhost:4444`. If you are using an older
version of MacOS, you can install "RealVNC Viewer" either using -`homebrew cask install vnc-viewer` or from the DMG file
on the RealVNC site.
If asked for a password, you should use `secret`.

Now run the tests, and you should be able to observe the browser activity.

## Performance

Performance is not as good as a dedicated VM, for a number of reasons, but mostly docker related rather than Lando.

We have made some attempt to improve this by using `excludes` within `.lando.yml` to exclude folders that are mostly
written by server and we do not need them.

On local, it has been found that updating docker preferences on Mac to only mount project folder and $HOME/.lando
folder into containers.

1. Change `~/.lando/config.yml` and set home to empty, so home directory wont be loaded. The content of this file should be,
```
home: ''
```

2. `(Already applied)` Once #1 is done, the ssh keys are not accessible to lando as home directory is unmounted. This fix https://github.com/lando/lando/issues/478#issuecomment-654634511 has been added for now as part of `.lando.yml` until lando has a better way to allow projects to manage ssh keys without sacrificing performance by mounting entire `home` directory.

NOTE: Be careful about the Experimental features in Docker dashboard. The assumption is we have them turned off.

For reference:
- https://github.com/lando/lando/issues/478#issuecomment-654634511
- https://github.com/lando/lando/issues/2635#issuecomment-877473886
- https://docs.lando.dev/config/performance.html
- https://github.com/lando/lando/issues/763

## Text Editors
### VS Code
1. In the XDebug configuration, make sure you have the following
```
"port": 9003,
"pathMappings": {
  "/app/": "${workspaceFolder}"
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
