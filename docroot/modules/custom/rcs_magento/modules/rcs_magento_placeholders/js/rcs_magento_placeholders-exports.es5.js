// @codingStandardsIgnoreFile
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
        request.headers.push(["Store", drupalSettings.alshayaRcs.commerceBackend.languagePrefix[drupalSettings.path.currentLanguage]]);

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

    default:
      console.log(
        `Entity type ${drupalSettings.rcsPage.type} not supported for get_entity.`
      );
      return result;
  }

  const response = await rcsCommerceBackend.invokeApi(request);
  if (response.data.products.total_count) {
    result = response.data.products.items[0];
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

      request.data = JSON.stringify({
        query: `{category(id: ${drupalSettings.alshayaRcs.navigationMenu.rootCategory}) {
            ${drupalSettings.alshayaRcs.navigationMenu.query}
          }
        }`
      });

      response = await rcsCommerceBackend.invokeApi(request);
      // Get exact data from response.
      if (response !== null) {
        // @todo: Need to verify the structure with MDC team.
        result = response.data.category.children[0].children;
      }
      break;

    default:
      console.log(`Placeholder ${placeholder} not supported for get_data.`);
      break;
  }

  return result;
};
