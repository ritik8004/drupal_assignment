Scripts for cron activities on Acquia Cloud.
================================================================================

Acquia cloud allows us to create/manage cron jobs by calling APIs through php scripts.
This file lists certain commands which are helpful to manage cron activities.

### Get Credentials first

To perform any operation, the first step is to get the credentials from acquia cloud and store them in a settings file. Here are the detailed steps - 

1. Sign in to Cloud Platform using your email address and Acquia password.
2. Click your user avatar in the upper right corner, and then click Account Settings.
3. On the Profile page, click API Tokens.
4. Provide a human-readable label for your API token, and click Create Token. Cloud Platform will generate an API Key and API secret for you.
5. Record a copy of your API Key and API secret, as you can’t retrieve them after closing your browser tab.
6. Create a file with name 'acquia_cloud_api_creds.php' under home directory.
7. Open file and paste your api key and api secret key in the below format and save -

```
$_clientId = [client_id];
$_clientSecret = ['client_secret'];
```

### Get all applications/stacks 

We can get all the stack available in acquia cloud by running this command - 
```
php scripts/cloud_config/getApplications.php
```

It will list down all stacks consisting of application uuid and application name.

### Get all environments of a particular application

We can get all the environments available under a particular stack by running this command - 
```
php scripts/cloud_config/getEnvironments.php [application_uuid]
```

Here we pass application_uuid as argument and output we get is list of all environments with environment uuid and
environment name.

### Create a cron job

We can create a cron job by running script in createCron.php. We require to pass environment uuid, cron command, cron frequency, cron label and cron server id as arguments. Command - 
```
php scripts/cloud_config/createCron.php [env_uuid] [cron_command] [cron_frequency] [cron_label] [cron_server_id]
```
Please note that cron_server_id will be used only for prod environment. For non-prod environments, it will be blank.

Here is an example command - 
```
php scripts/cloud_config/createCron.php ["5268-aa9e"] "/var/www/html/${AH_SITE_NAME}/scripts/cron/cron_flock.sh drush-delete-entity-cachetags acsf-tools-ml 'delete-entity-cachetags'" "0 0 * * 0" "Delete Entity Cachetags"
```

### Get all cron tasks of environment

We can list all cron tasks of a particular environment by simply passing environment uuid as argument. Here is the
command for that - 
```
php scripts/cloud_config/getCronTasks.php [environment_uuid]
```

### Get webserver name of environment

We can get server machine name of environment by running this command - 
```
php scripts/cloud_config/getWebServers.php [environment_uuid]
```

Here we pass environment_uuid as argument and output will be server name.

### Copy cron tasks from one environment to another 

We can copy all cron tasks of one environment to another environment of a particular stack by running this command  -
```
php scripts/cloud_config/copyCronTasks.php [source_env] [target_env]
```

Here source_env is environment uuid from where we are copying and target_env_id is environment uuid to which we want cron jobs to be copied in.