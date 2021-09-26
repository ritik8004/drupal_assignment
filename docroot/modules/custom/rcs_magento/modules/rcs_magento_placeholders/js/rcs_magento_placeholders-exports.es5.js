// @codingStandardsIgnoreFile

/**
 * Utility function to redirect to page.
 *
 * @param {string} url
 *   The url to redirect to.
 */
function redirectToPage(url) {
  const location = rcsWindowLocation();
  location.href = url;
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
      relative_url
    }}`
  });

  let response = await rcsCommerceBackend.invokeApi(request);
  if (response.data.urlResolver === null) {
    redirectToPage(`${drupalSettings.alshayaRcs['404_page']}?referer=${rcsWindowLocation().pathname}`);
  }
  else if ([301, 302].includes(response.data.urlResolver.redirectCode)) {
    redirectToPage(response.data.urlResolver.relative_url);
  }
  else {
    console.log(`No route/redirect found for ${urlKey[1]}.html.`);
  }
}

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
  let urlKey = '';

  switch (pageType) {
    case 'product':
      request.uri += "graphql";
      request.method = "POST";
      request.headers.push(["Content-Type", "application/json"]);
      request.headers.push(["Store", drupalSettings.alshayaRcs.commerceBackend.store]);

      urlKey = rcsWindowLocation().pathname.match(/buy-(.*?)\./);
      request.data = JSON.stringify({
        query: `{ products(filter: { url_key: { eq: "${urlKey[1]}" }}) ${rcsPhGraphqlQuery.products}}`
      });

      break;

    case 'category':
      // Prepare request parameters.
      request.uri += "graphql";
      request.method = "POST";
      request.headers.push(["Content-Type", "application/json"]);

      urlKey = rcsWindowLocation().pathname.match(/shop-(.*?)\/?$/);
      request.data = JSON.stringify({
        query: `{ categories(filters: { url_path: { eq: "${urlKey[1]}" }}) ${rcsPhGraphqlQuery.categories}}`
      });

      break;

    case 'promotion':
      // Prepare request parameters.
      request.uri += "graphql";
      request.method = "POST",
        request.headers.push(["Content-Type", "application/json"]);
      // @todo Remove the URL match once we get proper URL of promotion.
      const promotionUrlKey = rcsWindowLocation().pathname.match(/promotion\/(.*?)\/?$/);
      request.data = JSON.stringify({
        query: `{ promotionUrlResolver(url_key: "${promotionUrlKey[1]}") ${rcsPhGraphqlQuery.promotions}}`
      });

    default:
      console.log(
        `Entity type ${pageType} not supported for get_entity.`
      );
      return result;
  }

  let response = await rcsCommerceBackend.invokeApi(request);
  if (pageType === "product") {
    if (response.data.products.total_count) {
      result = response.data.products.items[0];
      RcsPhStaticStorage.set('product_' + result.sku, result);
    }
    else {
      await handleNoItemsInResponse(request, urlKey);
    }
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

    default:
      console.log(`Placeholder ${placeholder} not supported for get_data.`);
      break;
  }

  return result;
};
