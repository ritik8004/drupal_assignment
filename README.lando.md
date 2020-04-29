# Running with Lando for local development

It is possible to work with Lando for local development.

However, at the time of writing, it does require some manual steps, and there are some caveats - see below.

Using Lando should be considered EXPERIMENTAL. If you hit issues you could be mostly on your own, so don't depend on
this for your work if you're not already fairly comfortable with docker and lando.

Having said that, it'd be great to get people using it and fixing issues.

## Manual Setup

### Host resolution

Because we're not using .lndo URLs which get resolved by public DNS, we need to edit our own /etc/hosts files to add
entries for the Alshaya sites - in the same way that the Vagrant VM plugin does this for us.

### SSH

When I tried to run certain BLT commands requiring SSH access (such as local:sync) I found that Lando had not forwarded
my ssh keys as expected.

I found that the quickest workaround to this is to copy your ssh keys into `lando.config.userConfRoot/keys` - in probably
all cases, this will mean `$HOME/.lando/keys` - and then rebuild using `lando rebuild`.

Unfortunately, I couldn't find a way to get ssh agent forwarding going, so password protected keys will require the
password on each use, which is a minor irritation.

## Notes on Services

### MySQL

When the mysql service is started, commands are run to create databases for the sites if they don't already exist. These
commands can be found in the service config in the lando file. If you're adding new sites, as things stand, you'll
need to add another line here to create your database.

### Varnish

There is a separate varnish file for Lando in `architecture/varnish/varnish-4-lando.vcl`. The existing file does not
compile when using Lando.

## Tooling

### BLT

We've provided BLT tooling so that you can run BLT commands inside the container using `lando blt <command>`.

However, in the case of the frontend build tools, these are executed within a node container. Therefore, we're not
actually executing the BLT commands here - we're doing what the BLT commands would ultimately do, which is running some
 scripts from our `blt/scripts` folder.

This is not ideal since it means we could get out of date if the blt code in that area changes.

### Performance

Performance is not as good as a dedicated VM, for a number of reasons, but mostly docker related rather than Lando.

We have made some attempt to improve this by using excludes within .lando.yml to exclude vendor folder from shares.
Instead, it's contents are copied into the container at build time. However, this does mean that containers must be
rebuilt after each composer operation that changes the contents of your vendor folder.

On local, it has been found that updating docker preferences on mac to only mount project folder and $HOME/.lando
folder into containers.

- For reference: https://docs.lando.dev/config/performance.html

