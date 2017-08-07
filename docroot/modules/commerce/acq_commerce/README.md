# acq-commerce-drupal
Acquia Commerce Drupal Connector modules.

[![Build Status](
https://travis-ci.com/acquia/acm-drupal-modules.svg?token=amJVDynZCLNxDr5JthoJ&branch=8.x-1.x
)](https://travis-ci.com/acquia/acm-drupal-modules)

## Summary
These modules will connect to the Acquia Commerce Conductor to provide a better
commerce experience on Drupal.

## Setup
0. Build Drupal Docker container.
1. Log in Drupal as administrator with permission to enable modules.
2. Go to Admin > Extend (/admin/extend) and enable
all modules in Commerce group.
3. Go to Admin > Configuration > 
Simple OAuth (/admin/config/people/simple_oauth).
4. Generate RSA keys and set paths to them.
5. Create new OAuth client
(/admin/config/people/simple_oauth/oauth2_client/add)
,fill label, select user, fill secret 
and check Administrator scope. Selected user will be used for creating content.
 Copy client uuid, secret and user
credentials to middleware configuration.
6. Go to Admin > Commerce > Configuration > Conductor Settings and fill in 
middleware details.
8. (optional) If you want to run page behind Basic Auth, download 
[Shield](https://www.drupal.org/project/shield) 
module and apply 
[patch](
https://www.drupal.org/files/issues/exclude_include_pages-2822720-15.patch
).
