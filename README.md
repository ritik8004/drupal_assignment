# Alshaya

ACSF D8 commerce project with Magento integration done via Acquia Conductor.
Multi brands and multi countries solution for selling Alshaya products online.

## BLT

Please see the [BLT documentation](http://blt.readthedocs.io/en/latest/) for information on build, testing, and deployment processes.

## Resources

* JIRA - link me!
* GitHub - link me!
* Acquia Cloud subscription - link me!
* TravisCI - link me!

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
* Install Ansible: `brew install ansible`
* Run:
  * `...`
