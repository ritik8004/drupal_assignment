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
    query: `{urlResolver(url: "${urlKey}") {
      redirectCode
      relative_url
    }}`
  });

  let response = await rcsCommerceBackend.invokeApi(request);
  if (response.data.urlResolver === null) {
    rcsRedirectToPage(`${drupalSettings.alshayaRcs['404_page']}?referer=${rcsWindowLocation().pathname}`);
  }
  else if ([301, 302].includes(response.data.urlResolver.redirectCode)) {
    rcsRedirectToPage(response.data.urlResolver.relative_url);
  }
  else {
    // @todo use DataDog https://alshayagroup.atlassian.net/browse/CORE-34720
    console.log(`No route/redirect found for ${urlKey}.`);
  }
}

exports.getEntity = async function getEntity(langcode) {
  const pageType = rcsPhGetPageType();
  if (!pageType) {
    return;
  }

  const request = {
    uri: 'graphql',
    method: 'POST',
    headers: [
      ["Content-Type", "application/json"],
    ],
  };

  let result = null;
  let response = null;
  let urlKey = drupalSettings.rcsPage.fullPath;

  switch (pageType) {
    case 'product':
      // Add extra headers.
      request.headers.push(["Store", drupalSettings.alshayaRcs.commerceBackend.store]);

      // Remove .html suffix from the full path.
      let prodUrlKey = urlKey.replace('.html', '');

      // Build query.
      request.data = JSON.stringify({
        query: `{ products(filter: { url_key: { eq: "${prodUrlKey}" }}) ${rcsPhGraphqlQuery.products}}`
      });

      // Fetch response.
      response = await rcsCommerceBackend.invokeApi(request);
      if (response && response.data.products.total_count) {
        result = response.data.products.items[0];
        RcsPhStaticStorage.set('product_' + result.sku, result);
      }
      else {
        await handleNoItemsInResponse(request, urlKey);
      }
      break;

    case 'category':
      // Build query.
      request.data = JSON.stringify({
        query: `{ categories(filters: { url_path: { eq: "${urlKey}" }}) ${rcsPhGraphqlQuery.categories}}`
      });

      // Fetch response.
      response = await rcsCommerceBackend.invokeApi(request);
      if (response && response.data.categories.total_count) {
        result = response.data.categories.items[0];
      }
      else {
        await handleNoItemsInResponse(request, urlKey);
      }
      break;

    case 'promotion':
      // Build query.
      request.data = JSON.stringify({
        query: `{ promotionUrlResolver(url_key: "${urlKey}") ${rcsPhGraphqlQuery.promotions}}`
      });

      // Fetch response.
      response = await rcsCommerceBackend.invokeApi(request);
      if (response.data.promotionUrlResolver) {
        result = response.data.promotionUrlResolver;
      }
      if (!result || (typeof result.title !== 'string')) {
        await handleNoItemsInResponse(request, urlKey);
      }
      break;

    default:
      console.log(
        `Entity type ${pageType} not supported for get_entity.`
      );
      return result;
  }

  if (result !== null) {
    // Creating custom event to to perform extra operation and update the result
    // object.
    const updateResult = new CustomEvent('alshayaRcsUpdateResults', {
      detail: {
        result: result,
        pageType: pageType,
      }
    });

    // To trigger the Event.rcs_magento_placeholders-exports.es5.js
    document.dispatchEvent(updateResult);

    return updateResult.detail.result;
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
      // We do not need to do anything for breadcrumbs.
      // Adding this case to avoid console messages about breadcrumbs.
      break;

    case 'labels':
        request.uri += "graphql";
        request.method = "POST";
        request.headers.push(["Content-Type", "application/json"]);
        request.headers.push(["Store", drupalSettings.alshayaRcs.commerceBackend.store]);
        request.data = JSON.stringify({
          query: `query{
            amLabelProvider(productIds: [${params.productIds}], mode: PRODUCT){
              items{
                image
                name
                position
              }
            }
          }`
        });

        response = await rcsCommerceBackend.invokeApi(request);
        result = response.data.amLabelProvider;
      break;

    case 'product-recommendation':
      request.uri += "graphql";
      request.method = "POST";
      request.headers.push(["Content-Type", "application/json"]);
      request.headers.push(["Store", drupalSettings.alshayaRcs.commerceBackend.store]);
      request.data = JSON.stringify({
        query: `{ products(filter: { url_key: { eq: "${params.url_key}" }}) ${rcsPhGraphqlQuery.products}}`
      });

      response = await rcsCommerceBackend.invokeApi(request);
      result = response.data.products.items[0];
      RcsPhStaticStorage.set('product_' + result.sku, result);
      break;

    default:
      console.log(`Placeholder ${placeholder} not supported for get_data.`);
      break;
  }

  return result;
};

exports.getDataAsync = function getDataAsync(placeholder, params, entity, langcode) {
  const request = {
    uri: '/graphql',
    method: 'GET',
    headers: [
      ['Content-Type', 'application/json'],
      ['Store', drupalSettings.alshayaRcs.commerceBackend.store],
    ],
  };

  let response = null;
  let result = null;

  switch (placeholder) {
    case 'products-in-style':
      request.method = 'POST';
      request.data = JSON.stringify({
        query: `{ products(filter: { style_code: { match: "${params.styleCode}" }}) ${rcsPhGraphqlQuery.products}}`
      });

      response = rcsCommerceBackend.invokeApiAsync(request);
      result = response.data.products.items;
      break;

    default:
      console.log(`Placeholder ${placeholder} not supported for get_data.`);
      break;
  }

  return result;
}
