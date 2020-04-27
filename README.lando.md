# Running with Lando for local development

It is possible to work with Lando for local development.

However, at the time of writing, it does require some manual steps, and there are some caveats.

## Services

### MySQL

At the moment, a database service is required for each site.  However, running that many containers would probably
kill performance of the local machine.

So at the moment, when switching sites, edit the `.lando.yml` file to add the correct database name and rebuild.

This is something that probably requires looking into.

## BLT

We've provided BLT tooling so that you can run BLT commands inside the container using `lando blt <command>`.

### BUT...SSH

However, when I tried to run commands requiring SSH access (such as local:sync) I found that Lando had not forwarded
my ssh keys as expected.

I found that the quickest workaround to this is to copy your ssh keys into `lando.config.userConfRoot/keys` - in probably
all cases, this will mean `$HOME/.lando/keys` - and then rebuild using `lando rebuild`.

Unfortunately, I couldn't find a way to get ssh agent forwarding going, so password protected keys will require the
password on each use, which is a minor irritation.

## Performance

Performance is not as good as a dedicated VM, for a number of reasons, but mostly docker related rather than Lando.

We have made some attempt to improve this by using excludes within .lando.yml to exclude vendor folder from shares.
Instead, it's contents are copied into the container at build time. However, this does mean that containers must be
rebuilt after each composer operation that changes the contents of your vendor folder.

On local, it has been found that updating docker preferencess on mac to only mount project folder and $HOME/.lando
folder into containers.

- For reference: https://docs.lando.dev/config/performance.html

