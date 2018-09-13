# Integration scripts

## Summary:

These scripts are utilities to execute operations on ACSF using the API.

## Setup:

The ACSF API requires the requests to be authenticated via a username and an
API key. For security reason, these information are ignored in the repository.
To execute the scripts, create a file `includes/global-api-settings.inc.sh` and
add the following content:
```
user: <username>
api_key: <api_key>
```
The username is your username on ACSF. Your API key is accessible in your
account page on ACSF UI. It is the same API key across all the environments of
the factory.

