// @codingStandardsIgnoreFile

// Static variable to store API response for the products in a page.
var staticProductData = null;

exports.getEntity = async function getEntity(langcode) {
  if (typeof drupalSettings.rcsPage === 'undefined') {
    return null;
  }

  const request = {
    uri: '',
    method: 'GET',
    headers: [],
  };

  let result = null;

  switch (drupalSettings.rcsPage.type) {
    case 'product':
      case 'entity':
        request.uri += "graphql";
        request.method = "POST",
        request.headers.push(["Content-Type", "application/json"]);
        request.headers.push(["Store", drupalSettings.alshayaRcs.commerceBackend.store]);

        const productUrlKey = rcsWindowLocation().pathname.match(/buy-(.*?)\./);
        // @todo: Make a config for this query and pass it from the backend.
        request.data = JSON.stringify({
          query: `{products(filter: {url_key: {eq: "${productUrlKey[1]}"}}) {
            total_count
            items {
                sku
                id
                type_id
                name
                url_key
                is_buyable
                price {
                    regularPrice {
                        amount {
                            currency
                            value
                        }
                    }
                    maximalPrice {
                        amount {
                            currency
                            value
                        }
                    }
                }
                brand_logo
                media_gallery {
                  url
                  label
                  ... on ProductVideo {
                    video_content {
                      media_type
                      video_provider
                      video_url
                      video_title
                      video_description
                      video_metadata
                    }
                  }
                }
                gtm_attributes {
                  id
                  name
                  variant
                  price
                  brand
                  category
                  dimension2
                  dimension3
                  dimension4
                }
                ... on ConfigurableProduct {
                  configurable_options {
                    id
                    label
                    position
                    use_default
                    attribute_code
                    product_id
                    values {
                      value_index
                      label
                    }
                  }
                  variants {
                    product {
                      id
                      sku
                      meta_title
                      stock_status
                      image {
                        url
                        label
                      }
                      sku
                      attribute_set_id
                      ... on PhysicalProductInterface {
                        weight
                      }
                      price {
                        regularPrice {
                          amount {
                            value
                            currency
                          }
                        }
                        maximalPrice {
                          amount {
                            value
                            currency
                          }
                        }
                      }
                      special_price
                      special_from_date
                      special_to_date
                      is_returnable
                      media_gallery {
                        url
                        label
                        ... on ProductImage {
                          url
                          label
                        }
                      }
                      small_image {
                        url
                        label
                      }
                      swatch_image
                      image {
                        url
                        label
                      }
                      thumbnail {
                        url
                        label
                      }
                      media_gallery {
                        url
                        label
                        ... on ProductVideo {
                          video_content {
                            media_type
                            video_provider
                            video_url
                            video_title
                            video_description
                            video_metadata
                          }
                        }
                      }
                      gift_message_available
                    }
                    attributes {
                      label
                      code
                      value_index
                    }
                  }
                }
            }
          }}`
        });

      break;

    case 'category':
      // Prepare request parameters.
      request.uri += "graphql";
      request.method = "POST",
      request.headers.push(["Content-Type", "application/json"]);

      const categoryUrlKey = rcsWindowLocation().pathname.match(/shop-(.*?)\/?$/);
      request.data = JSON.stringify({
        query: `{ categories( filters: { url_path: {eq: "${categoryUrlKey[1]}"}}) {
            total_count
            items {
              level
              name
              url_path
              description
              image
              breadcrumbs {
                category_name
                category_level
                category_url_key
                category_url_path
              }
            }
          }
        }`
      });

      break;

    default:
      console.log(
        `Entity type ${drupalSettings.rcsPage.type} not supported for get_entity.`
      );
      return result;
  }

  const response = await rcsCommerceBackend.invokeApi(request);
  if (drupalSettings.rcsPage.type == "product" && response.data.products.total_count) {
    result = response.data.products.items[0];
    setDataToStorage('product', result);
  }
  else if (drupalSettings.rcsPage.type == "category" && response.data.categories.total_count) {
    result = response.data.categories.items[0];
  }

  return result;
};

exports.getData = async function getData(placeholder, params, entity, langcode) {
  const request = {
    uri: '',
    method: 'GET',
    headers: [],
    language: langcode,
  };

  let response = null;
  let result = null;
  switch (placeholder) {
    // No need to fetch anything. The markup will be there in the document body.
    // Just return empty string so that render() function gets called later.
    case 'delivery-option':
      result = '';
      break;

    case 'navigation_menu':
      // Early return if the root category is undefined.
      if (typeof drupalSettings.alshayaRcs.navigationMenu.rootCategory === 'undefined') {
        return null;
      }

      // Prepare request parameters.
      request.uri += "graphql";
      request.method = "POST",
      request.headers.push(["Content-Type", "application/json"]);
      request.headers.push(["Store", drupalSettings.alshayaRcs.commerceBackend.store]);

      request.data = JSON.stringify({
        // @todo: we are using 'category' API for now which is going to be
        // deprecated, but only available API to support both 2.3 and 2.4
        // magento version, so as suggested we are using this for now but
        // need to change this when this got deprecated in coming magento
        // version and replace it with 'categoryList' magento API.
        query: `{category(id: ${drupalSettings.alshayaRcs.navigationMenu.rootCategory}) {
            ${drupalSettings.alshayaRcs.navigationMenu.query}
          }
        }`
      });

      response = await rcsCommerceBackend.invokeApi(request);
      // Get exact data from response.
      if (response !== null) {
        // Skip the default category data always.
        result = response.data.category.children[0].children;
      }
      break;

    default:
      console.log(`Placeholder ${placeholder} not supported for get_data.`);
      break;
  }

  return result;
};

/**
 * Stores the response from API call into storage.
 *
 * @param {string} entityType
 *   The entity type, eg. 'product'.
 * @param {object} data
 *   The entity data to store.
 */
function setDataToStorage(entityType, data) {
  switch (entityType) {
    case 'product':
      staticProductData[data.sku] = data;
      break;
  }
}

/**
 * Retrieves the given entity from storage.
 *
 * @param {string} entityType
 *   The entity type, eg. 'product'.
 * @param {string} entityId
 *   The identifier key for the entity, eg. sku value for product.
 *
 * @returns {Object}
 *   The stored entity data.
 */
function getDataFromStorage(entityType, entityId) {
  // We leave it undefined so that the checks in the existing code which check
  // for eg. the undefined value of drupalSettings.productInfo continue to work
  // as before.
  var data;

  switch (entityType) {
    case 'product':
      data = staticProductData[entityId];
      break;
  }

  return data;
}
exports.getDataFromStorage = getDataFromStorage;
