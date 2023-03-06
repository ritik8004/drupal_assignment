## JS Setup

We need JS file to enter data in React inputs (for instance OTP input).

For this we have added the compiled file in js directory, source of which is [here](../../../../tests/utilities/behat_user_events/js/user_events.js)

To update, modify in the architecture directory and compile from there which will output the file [here](js/user_events.bundle.js)

## Behat
When the automation scripts are executed, we provide the argument `behat=[key]` to tell the website that we are running
automated tests.

This will provide some extra functionalities i.e.
- Disable Captcha
- Allow users to be created without confirmation
- Add Js libraries used for testing

If for any reason we need to disable these extra functionalities, it can be done via Drupal settings.php on the server:
```
$settings['alshaya_behat_disabled'] = TRUE;
```
