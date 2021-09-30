exports.getEntity = async function getEntity(langcode) {
  const pageType = rcsPhGetPageType();
  if (!pageType) {
    return;
  }

  const request = {
    uri: '',
    method: 'GET',
    headers: [],
  };

  let result = null;

  switch (pageType) {
    case 'product':
      request.uri += "graphql";
      request.method = "POST";
      request.headers.push(["Content-Type", "application/json"]);
      request.headers.push(["Store", drupalSettings.alshayaRcs.commerceBackend.store]);
      // Remove .html suffix from the full path.
      const productUrlKey = drupalSettings.rcsPage.fullPath.replace('.html', '');
      request.data = JSON.stringify({
        query: `{ products(filter: { url_key: { eq: "${productUrlKey}" }}) ${rcsPhGraphqlQuery.products}}`
      });

      break;

    case 'category':
      // Prepare request parameters.
      request.uri += "graphql";
      request.method = "POST";
      request.headers.push(["Content-Type", "application/json"]);
      request.data = JSON.stringify({
        query: `{ categories(filters: { url_path: { eq: "${drupalSettings.rcsPage.fullPath}" }}) ${rcsPhGraphqlQuery.categories}}`
      });

      break;

    case 'promotion':
      // Prepare request parameters.
      request.uri += "graphql";
      request.method = "POST",
      request.headers.push(["Content-Type", "application/json"]);
      request.data = JSON.stringify({
        query: `{ promotionUrlResolver(url_key: "${drupalSettings.rcsPage.urlKey}") ${rcsPhGraphqlQuery.promotions}}`
      });

      break;

    default:
      console.log(
        `Entity type ${pageType} not supported for get_entity.`
      );
      return result;
  }

  const response = await rcsCommerceBackend.invokeApi(request);
  if (pageType === "product" && response.data.products.total_count) {
    result = response.data.products.items[0];
    RcsPhStaticStorage.set('product_' + result.sku, result);
  }
  else if (pageType === "category" && response.data.categories.total_count) {
    result = response.data.categories.items[0];
  }
  else if (drupalSettings.rcsPage.type === 'promotion' && response.data.promotionUrlResolver) {
    result = response.data.promotionUrlResolver;
    // Adding name in place of title so that RCS replace the placeholders
    // properly.
    result.name = result.title;
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

/**
 * Gets data attributes from rcs placeholders.
 *
 * @param placeholder
 *   The placeholder id.
 * @return {DOMStringMap}
 *   The data attributes.
 */
function getDataAttributes(placeholder) {
  const element = document.querySelector(`#rcs-ph-${placeholder}`);
  return element.dataset;
}

exports.getData = async function getData(placeholder, params, entity, langcode) {
  const request = {
    uri: '',
    method: 'GET',
    headers: [],
    language: langcode,
  };

  let response = null;
  let result = null;
  const dataAttributes = getDataAttributes(placeholder);
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

    case 'magazine_shop_the_story':
      request.uri += "graphql";
      request.method = "POST",
      request.headers.push(["Content-Type", "application/json"]);
      request.headers.push(["Store", drupalSettings.alshayaRcs.commerceBackend.store]);
      request.data = JSON.stringify({
        query: `{ products(filter: { sku: { in: ${dataAttributes.skus} }}) ${rcsPhGraphqlQuery.products}}`
      });

      response = await rcsCommerceBackend.invokeApi(request);
      // Get exact data from response.
      if (response !== null) {
        result = response.data.products.items;
      }
      break;

    case 'breadcrumb':
      // We do not need to do anything for breadcrumbs.
      // Adding this case to avoid console messages about breadcrumbs.
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
