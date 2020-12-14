Scripts for cron activities on Acquia Cloud.
================================================================================

Acquia cloud allows us to create/manage cron jobs by calling APIs through php scripts.
This file lists certain commands which are helpful to manage cron activities.

#Get Credentials first -

To perform any operation, the first step is to get the credentials from acquia cloud and store them in a settings file. Here are the detailed steps - 

1. Sign in to Cloud Platform using your email address and Acquia password.
2. Click your user avatar in the upper right corner, and then click Account Settings.
3. On the Profile page, click API Tokens.
4. Provide a human-readable label for your API token, and click Create Token. Cloud Platform will generate an API Key and API secret for you.
5. Record a copy of your API Key and API secret, as you can’t retrieve them after closing your browser tab.
6. Create a file with name 'acquia_cloud_api_creds.php' under home directory.
7. Open file and paste your api key and api secret key in the below format and save -

```
$_clientId = 'd26f32f9-6700-4e6f-baa0-b112c7c8fb70';
$_clientSecret = 'f4KOhAdDiQan2I82zYS8JPZ61apA2kiAkqUoo62y/us=';
```

#Get all applications/stacks - 

We can get all the stack available in acquia cloud by running this command - 
```php scripts/cloud_config/getApplications.php```

It will list down all stacks consisting of application id and application name.

#Get all environments of a particular application - 

We can get all the environments available under a particular stack by running this command - 
```php scripts/cloud_config/getEnvironments.php [application_id]```

Here we pass application_id as argument and output we get is list of all environments with environment id and
environment name. Example - 

#Create a cron job - 

We can create a cron job by running script in createCron.php. We require to pass environment_id, command, frequency and job name as arguments. Command - 
```php scripts/cloud_config/createCron.php [environment_id] [command] [frequency] [job_name]```

Here is an example command - 
```php scripts/cloud_config/createCron.php "5268-06063c00-aa9e-4b90-bba3-20b9fe0b1913" "/var/www/html/${AH_SITE_NAME}/scripts/cron/cron_flock.sh drush-delete-entity-cachetags acsf-tools-ml 'delete-entity-cachetags'" "0 0 * * 0" "Delete Entity Cachetags"```

#Get all cron tasks of environment- 

We can list all cron tasks of a particular environment by simply passing environment id as argument. Here is the
command for that - 
```php scripts/cloud_config/getCronTasks.php "4186-ebc04ed7-2045-4339-97d9-a56b3eb19e2a"```

#Get webserver name of environment - 

We can get server machine name of environment by running this command - 
```php scripts/cloud_config/getWebServers.php [environment_id]```

Here we pass environment_id as argument and output will be server name.

#Copy cron tasks from one environment to another - 

We can copy all cron tasks of one environment to another environment of a particular stack by running this command  -
```php scripts/cloud_config/copyCronTasks.php [source_env_id] [target_env_id]```

Here source_env_id is environment id from where we are copying and target_env_id is environment id to which we want cron jobs to be copied in.