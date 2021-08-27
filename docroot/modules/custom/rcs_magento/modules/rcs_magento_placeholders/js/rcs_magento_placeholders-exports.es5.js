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
                stock_status
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
                brand_logo_data {
                  url
                  alt
                  title
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
                meta_title
                meta_description
                meta_keyword
                og_meta_title
                og_meta_description
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
                category_ids_in_admin
                categories {
                  id
                  name
                  level
                  url_path
                  include_in_menu
                  breadcrumbs {
                    category_name
                    category_level
                    category_url_key
                    category_url_path
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
    RcsPhStaticStorage.set('product_' + result.sku, result);
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
      // @todo To optimize the multiple category API call.
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

    case 'breadcrumb':
      if (typeof entity.categories !== 'undefined') {
        // Get categories from entity.
        const { categories } = entity;
        // Get category flagged as `category_ids_in_admin`.
        result = categories.find((e) => {
          return entity.category_ids_in_admin.includes(e.id.toString());
        });
        // Move last crumb up.
        result.breadcrumbs.push({
          category_name: result.name,
          category_url_path: result.url_path,
        });
        // Set breadcrumb title.
        result.name = entity.name;
      }
      break;

    default:
      console.log(`Placeholder ${placeholder} not supported for get_data.`);
      break;
  }

  return result;
};
