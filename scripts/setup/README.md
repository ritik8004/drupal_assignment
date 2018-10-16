# Setup script

## Summary:

This folder contains a script designed to ease installation operations. Indeed,
after a site is installed via ACSF UI, only a simple whitelabel site is 
created. Multiple steps such as branding, country specific customization and
commerce data remain to be done.

## Operations:

In order to finalize the site install, multiple operations are done:
1. Enable the brand and country modules.
2. Configure authentication to allow Acquia Commerce Manager connection.
3. Synchronize commerce data (SKUs, products, categories, ...) from MDC.

On non-production environments, a default admin and a default webmaster users
are created to avoid individual account creation.

## Expected workflow:

#### Automatic
The script is automatically invoked after a site is installed via ACSF UI.
(see `factory-hooks/post-install/post-install.php`).

#### Manual
It is possible to manually execute the script in case something wrong happened.
`./scripts/setup/setup-fresh-site.sh <env> <url> <brand-code> <country-code>`
where `<env>` is the current ACSF environment (01dev, 01test, 01uat, ...),
`<url>` is the site url, `<brand-code>` is the brand (mc, vs, hm, ...) and
`<country-code>` is the country (kw, sa, ae).

## Override / Disable:

There may be some situation where we don't want to execute the post-install 
operations or we may want to override the brand or country to be configured.
(For example, we may want to install a Kuwait Mothercare site on 
vbkw-<env>.factory.alshaya.com domain for testing purpose).

To by-pass or override post-install operations, simply create a 
`/home/alshaya/post-install-override.txt` file. If the file is empty, the
operations will be completely by-passed. To override the configuration, fill
the file content with following format:
```
action: disable|override
brand_code: mc|hm|...
country_code: kw|sa|ae
```
It is possible of course to override only one of the 2 arguments.

:warning: Overriding the brand_code and/or country_code may lead to unexpected
results given the ACM and MDC configuration will remain based on the domain.

:warning: In case of shared environment (dev/test for example), the override
file will impact all the environments.

## Logging:

By default, the script does not display anything in the terminal (it is not
possible to view it on ACSF anyway). To ease debugging and monitoring, 
operations results are redirected to `/home/alshaya/site-install.log`. This
file is cleared at the begining of each site post-install process so it is
only possible to access the latest post-install logs.
