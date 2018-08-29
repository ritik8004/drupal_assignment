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
