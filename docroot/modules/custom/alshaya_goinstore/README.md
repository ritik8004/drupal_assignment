# Module file for activating add to basket via GoInStore widget.

## GoInStore
- GoInStore is a video retailing service provider.
- Widget integration of this service is done through tag management system.

## Add to basket via GoInStore
- The add to basket feature allows advisors on a GoInStore video call to add products directly into customers' basket.

## Pre-requisites for GoInStore to setup add to basket

- To enable the Add to Basket feature via GoInStore, our product catalogue should be already integrated into the GoInStore system.
- The add to basket feature works by triggering a configured global JavaScript snippet on our site that uses the Product Id from our product catalogue to add that item to the basket.

## Configurations

- Enable `alshaya_goinstoe` module
- By default config is set as fasle to enable the config run drush command: `drush -l <url> cset alshaya_goinstore.settings enabled TRUE --input-format=yaml -y`
- After the module and config is enabled, a global JavaScript snippet that mocks our add to bag functionality is made available for Goinstore widget, to trigger guided purchase on our site during a video call.
- Goinstore widget will trigger add to basket by calling our global JavaScript function `Drupal.goinstore(ParentSku, childSku,qty)`
