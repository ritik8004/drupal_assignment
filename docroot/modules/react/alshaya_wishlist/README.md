# Module for Alshaya Product Wishlist Feature.

## Pre-requisites to enable this feature

- `Algolia V2` should be enabled (we use algolia `algolia_product_list_index` index for this feature)
- `alshaya_add_to_bag` module need to be enabled (add to bag feature on PLP can be kept disabled)
- The algolia product list index must have SKU as a facet filter. This should be filterOnly
## Configurations

- After `alshaya_wishlist` module is enabled or disable manage the configurations from here `admin/config/alshaya/wishlist`.