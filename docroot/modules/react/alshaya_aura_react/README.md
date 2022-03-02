# Module for Alshaya Aura loyalty.

## Configurations

* After `alshaya_aura_react module` is enabled, use config `aura_enabled` to enable/disable AURA.
* Configure `App Store` and `Google Play` links across AURA sections from here: `admin/config/alshaya/aura-loyalty`
* Configure `Learn More` link of AURA Rewards pop up in Header from here: `admin/config/alshaya/aura-loyalty`
* Configure `Terms and Conditions` link of AURA Sign up pop up from here: `admin/config/alshaya/aura-loyalty`
* Configure time limit (in months) for user's `Reward Activity` transaction history using config `aura_reward_activity_time_limit_in_months`
* Configure content of `Loyalty benefits` section on `My AURA` page from here `admin/config/alshaya/aura-loyalty-benefits`
* Configure unsupported aura payment methods using drush: `drush -l <url> cset alshaya_aura_react.settings aura_unsupported_payment_methods.<index> <value>` eg: `drush -l <url> cset alshaya_aura_react.settings aura_unsupported_payment_methods.2 tabby`
