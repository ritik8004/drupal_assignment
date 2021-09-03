// @codingStandardsIgnoreFile

/**
 * Utility function to redirect to 404 page.
 */
function redirectTo404Page() {
  const location = rcsWindowLocation();
  location.href = `${drupalSettings.alshayaRcs['404_page']}?referer=${rcsWindowLocation().pathname}`;
}

/**
 * Handle 404 case on initial request.
 *
 * @param {object} request
 *   The request object.
 * @param {string} urlKey
 *   The url key.
 */
async function handleNoItemsInResponse(request, urlKey) {
  request.data = JSON.stringify({
    query: `{urlResolver(url: "${urlKey[1]}.html") {
      redirectCode
    }}`
  });

  let response = await rcsCommerceBackend.invokeApi(request);
  if (response.data.urlResolver === null) {
    redirectTo404Page();
  }
}

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
  let urlKey = '';

  switch (drupalSettings.rcsPage.type) {
    case 'product':
    case 'entity':
      request.uri += "graphql";
      request.method = "POST";
      request.headers.push(["Content-Type", "application/json"]);
      request.headers.push(["Store", drupalSettings.alshayaRcs.commerceBackend.store]);

      urlKey = rcsWindowLocation().pathname.match(/buy-(.*?)\./);
      request.data = JSON.stringify({
        query: `{ products(filter: { url_key: { eq: "${urlKey[1]}" }}) ${rcsGraphqlQueryFields.products}}`
      });

      break;

    case 'category':
      // Prepare request parameters.
      request.uri += "graphql";
      request.method = "POST";
      request.headers.push(["Content-Type", "application/json"]);

      urlKey = rcsWindowLocation().pathname.match(/shop-(.*?)\/?$/);
      request.data = JSON.stringify({
        query: `{ categories(filters: { url_path: { eq: "${urlKey[1]}" }}) ${rcsGraphqlQueryFields.categories}}`
      });

      break;

    default:
      console.log(
        `Entity type ${drupalSettings.rcsPage.type} not supported for get_entity.`
      );
      return result;
  }

  let response = await rcsCommerceBackend.invokeApi(request);
  if (drupalSettings.rcsPage.type == "product") {
    if (response.data.products.total_count) {
      result = response.data.products.items[0];
      RcsPhStaticStorage.set('product_' + result.sku, result);
    }
    else {
      await handleNoItemsInResponse(request, urlKey);
    }
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

    default:
      console.log(`Placeholder ${placeholder} not supported for get_data.`);
      break;
  }

  return result;
};
