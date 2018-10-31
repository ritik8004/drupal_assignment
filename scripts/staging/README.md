# Staging scripts

## Summary:

This folder contains a set of utility scripts designed to ease staging
operations for the sites of Alshaya Factory. Staging a site from production to 
a sub environment is a feature provided by ACSF but because of the Alshaya 
specificity, additional operation are required post stage.

Indeed, some configuration may differ from production but specially the
products catalog and other commerce data given the target environment is not 
connected to the same Acquia Commerce Manager (ACM) and Magento (MDC) stream. 
For these reasons, the staged sites must be cleaned from production data and 
synchronized with the appropriate ACM and MDC stream.

## Staging modes:

#### Hard stage
This mode does some operation on the target environment before staging the 
selected sites. It deletes ALL the sites of the target environment and switch
the code base to the same branch/tag that is deployed on production. Doing that
way, the staged sites are iso-prod after staging.

#### Soft stage
This mode only stage the selected sites on the target environment. If these
already exist here, there will be deleted first. But the other sites remain
untouched and the code base as well. **It is critical to understand the staged
database may not be compatible with the deployed code unless database updates
are performed.**

## Operations:

In order to reset a site, multiple operations are done:
1. Take a database dump (so it can be restored in case the reset process fails).
2. Enable some developer modules, disable shield, disable search indexes to 
speed up some processes.
3. Delete commerce data (SKUs, products, categories, ...).
4. Synchronize commerce data (SKUs, products, categories, ...) from MDC.
5. Re-enable search indexes and index the new content.
6. Take a database dump (so it can be restored later to test updates).

Because some of these operations are long (specially the product sync), the
sites reset are parallelized so one heavy site does not block the reset of the
next ones (in case of multiple sites).

## Scripts:

These scripts must be executed from the target environment after connecting via
ssh.

#### After hard stage
After a hard stage, all the sites of the factory must be reset:
`./scripts/staging/reset-all-sites-post-stage.sh "<env>"` where `<env>` is the 
current ACSF environment (01dev, 01test, 01uat, ...).

#### After soft stage
After a soft stage, only the selected site must be reset:
`./scripts/staging/reset-individual-site-post-stage.sh "<env>" "<site-code>"` 
where `<env>` is the current ACSF environment (01dev, 01test, 01uat, ...) and
`<site-code>` is the site ID used to stage the site (hmkw, mcsa, vsae, ...).

In case multiple sites have been soft staged at the same time, the script
must be launched for each site individually.

#### Technical details
The 2 scripts described (`reset-all-sites-post-stage.sh` and 
`reset-individual-site-post-stage.sh`) are simply wrapper scripts both using
`reset-post-stage.sh`. They are only building and validating the list of sites
to be reset. The `reset-post-stage.sh` script itself is a wrapper calling
multiple sub-scripts stored in `scripts/staging/sub-sh/`.

## Expected workflow:

#### Hard stage
* From [production factory UI](https://www.alshaya.acsitefactory.com/admin/gardens/staging/deploy),
select the target environment, select the sites to be staged, click the 
"Wipe target environment" checkbox and submit the form.
* Wait for the target environment to be wiped and the sites to be staged.
* Connect to the target environment via ssh and launch the `reset-all-sites-post-stage.sh`
script. Ideally, use [`screen`](https://www.gnu.org/software/screen/manual/screen.html#Invoking-Screen)
so the process is not stopped in case ssh connection is lost.
* Finally, deploy the new code using "Code and databases" option.

#### Soft stage - Manual
* From [production factory UI](https://www.alshaya.acsitefactory.com/admin/gardens/staging/deploy),
select the target environment, select the sites to be staged and submit the
form.
* Wait for the sites to be staged.
* Connect to the target environment via ssh and run database updates on the
staged sites.
* Launch the `reset-individual-site-post-stage.sh` script for each staged site.
Ideally, use [`screen`](https://www.gnu.org/software/screen/manual/screen.html#Invoking-Screen)
so the process is not stopped in case ssh connection is lost.

#### Soft stage - Script
* Connect to the target environment via ssh.
* Launch the `soft-stage.sh` script. Ideally, use [`screen`](https://www.gnu.org/software/screen/manual/screen.html#Invoking-Screen)
so the process is not stopped in case ssh connection is lost.
`./../script/soft-stage.sh "vsae;mckw,mcae;hmae;bbwsa" "01dev"` for example.
