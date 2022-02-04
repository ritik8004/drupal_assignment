/**
 * Handle 404 case on initial request.
 *
 * @param {object} request
 *   The request object.
 * @param {string} urlKey
 *   The url key.
 */
async function handleNoItemsInResponse(request, urlKey) {
  request.data = prepareQuery(`{urlResolver(url: "${urlKey}") {
      redirectCode
      relative_url
    }}`
  );

  let response = await rcsCommerceBackend.invokeApi(request);
  let rcs404 = `${drupalSettings.rcs['404Page']}?referer=${rcsWindowLocation().pathname}`;

  if (response.data.urlResolver === null) {
    return rcsRedirectToPage(rcs404);
  }

  if ([301, 302].includes(response.data.urlResolver.redirectCode)) {
    return rcsRedirectToPage(response.data.urlResolver.relative_url);
  }

  RcsEventManager.fire('error', {
    'level': 'warning',
    'message': `No route/redirect found for ${urlKey}.`,
    'context': {
      'method': 'handleNoItemsInResponse',
    },
  });

  // Redirect to 404 page when proper redirection was not received.
  return rcsRedirectToPage(rcs404);
}

/**
 * Prepares query string for GraphQL GET request.
 *
 * @param {string} data
 *   The string to prepare.
 *
 * @returns {string}
 *   The compressed and URL safe string.
 */
function prepareQuery(data) {
  // Remove unnecessary characters.
  let query = global.rcsQueryCompressor(data);
  // Encode to valid uri format.
  query = encodeURIComponent(query);
  return `query=${query}`;
}

exports.getEntity = async function getEntity(langcode) {
  const pageType = rcsPhGetPageType();
  if (!pageType) {
    return;
  }

  const request = {
    uri: '/graphql',
    method: 'GET',
    headers: [
      ["Content-Type", "application/json"],
      ["Store", drupalSettings.rcs.commerceBackend.store],
    ],
  };

  let result = null;
  let response = null;
  let urlKey = drupalSettings.rcsPage.fullPath;

  switch (pageType) {
    case 'product':
      // Remove .html suffix from the full path.
      let prodUrlKey = urlKey.replace('.html', '');

      // Compress the query.
      request.data = prepareQuery(`{ products(filter: { url_key: { eq: "${prodUrlKey}" }}) ${rcsPhGraphqlQuery.products} }`);

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
      request.data = prepareQuery(`{ categories(filters: { url_path: { eq: "${urlKey}" }}) ${rcsPhGraphqlQuery.categories}}`);

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
      request.data = prepareQuery(`{ promotionUrlResolver(url_key: "${urlKey}") ${rcsPhGraphqlQuery.promotions}}`);

      // Fetch response.
      response = await rcsCommerceBackend.invokeApi(request);
      if (response.data.promotionUrlResolver) {
        result = response.data.promotionUrlResolver;
      }
      if (!result || (typeof result.title !== 'string')) {
        rcsRedirectToPage(`${drupalSettings.rcs['404Page']}?referer=${rcsWindowLocation().pathname}`);
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
    const updateResult = RcsEventManager.fire('rcsUpdateResults', {
      detail: {
        result: result,
        pageType: pageType,
      }
    });

    return updateResult.detail.result;
  }

  return result;
};

exports.getData = async function getData(placeholder, params, entity, langcode, markup, loaderOnUpdates = false) {
  const request = {
    uri: '/graphql',
    method: 'GET',
    headers: [
      ['Content-Type', 'application/json'],
      ['Store', drupalSettings.rcs.commerceBackend.store],
    ],
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
      // @todo: we are using 'category' API for now which is going to be
      // deprecated, but only available API to support both 2.3 and 2.4
      // magento version, so as suggested we are using this for now but
      // need to change this when this got deprecated in coming magento
      // version and replace it with 'categoryList' magento API.
      request.data = prepareQuery(`{category(id: ${drupalSettings.alshayaRcs.navigationMenu.rootCategory})
        ${rcsPhGraphqlQuery.navigationMenu}
      }`);

      response = await rcsCommerceBackend.invokeApi(request);
      // Get exact data from response.
      if (response !== null) {
        // Skip the default category data always.
        result = response.data.category.children[0].children;
      }
      break;

    case 'field_magazine_shop_the_story':
      request.data = prepareQuery(`{ products(filter: { sku: { in: ${params.skus} }}) ${rcsPhGraphqlQuery.magazine_shop_the_story}}`);

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

    case 'labels':
        request.data = prepareQuery(`{
            amLabelProvider(productIds: [${params.productIds}], mode: PRODUCT){
              items{
                image
                name
                position
                product_id
              }
            }
          }`);

      response = await rcsCommerceBackend.invokeApi(request);
      result = response.data.amLabelProvider;
      break;

    case 'product-recommendation':
      // @TODO Review this query to use only fields that are required for the display.
      request.data = prepareQuery(`{ products(filter: { sku: { eq: "${params.sku}" }}) ${rcsPhGraphqlQuery.products}}`);

      response = await rcsCommerceBackend.invokeApi(request);
      result = response.data.products.items[0];
      RcsPhStaticStorage.set('product_' + result.sku, result);

      break;

    case 'order_teaser':
      // @todo To use graphql query to get the order details.
      break;

    // Get the product data for the given sku.
    case 'product':
      // Build query.
      const operator = typeof params.op !== 'undefined' ? params.op : 'eq';
      const filterValue = operator === 'in' ? JSON.stringify(params.sku) : `"${params.sku}"`;
      request.data = prepareQuery(`{ products(filter: { sku: { ${operator}: ${filterValue} }}) ${rcsPhGraphqlQuery.products}}`);

      response = await rcsCommerceBackend.invokeApi(request);
      result = response.data.products.items;

      break;
    default:
      console.log(`Placeholder ${placeholder} not supported for get_data.`);
      break;
  }

  if ((result && result !== null)
    || placeholder === 'order_teaser') {
    // Display loader.
    if (loaderOnUpdates) {
      RcsEventManager.fire('startLoader');
    }

    // Creating custom event to to perform extra operation and update the result
    // object.
    const updateResult = RcsEventManager.fire('rcsUpdateResults', {
      detail: {
        result: result,
        params: params,
        placeholder: placeholder,
      }
    });

    // Hide loader.
    if (loaderOnUpdates) {
      RcsEventManager.fire('stopLoader');
    }

    return updateResult.detail.result;
  }

  return result;
};

exports.getDataSynchronous = function getDataSynchronous(placeholder, params, entity, langcode) {
  const request = {
    uri: '/graphql',
    method: 'GET',
    headers: [
      ['Content-Type', 'application/json'],
      ['Store', drupalSettings.rcs.commerceBackend.store],
    ],
  };

  let response = null;
  let result = null;

  switch (placeholder) {
    case 'products-in-style':
      request.data = prepareQuery(`{ products(filter: { style_code: { match: "${params.styleCode}" }}) ${rcsPhGraphqlQuery.products}}`);

      response = rcsCommerceBackend.invokeApiSynchronous(request);
      result = response.data.products.items;
      break;

    // Get the product data for the given sku.
    case 'product':
      // Build query.
      const operator = typeof params.op !== 'undefined' ? params.op : 'eq';
      const filterValue = operator === 'in' ? JSON.stringify(params.sku) : `"${params.sku}"`;
      request.data = prepareQuery(`{ products(filter: { sku: { ${operator}: ${filterValue} }}) ${rcsPhGraphqlQuery.products}}`);

      response = rcsCommerceBackend.invokeApiSynchronous(request);

      if (response && response.data.products.total_count) {
        response.data.products.items.forEach(function (product) {
          RcsEventManager.fire('rcsUpdateResults', {
            detail: {
              result: product,
            }
          });
        });
      }
      break;

    case 'product-option':
      const staticKey = `product_options_${params.attributeCode}`;
      const staticOption = RcsPhStaticStorage.get(staticKey);

      if (staticOption !== null) {
        return staticOption;
      }

      request.data = prepareQuery(`{ customAttributeMetadata(attributes: { entity_type: "4", attribute_code: "${params.attributeCode}" }) ${rcsPhGraphqlQuery.product_options}}`);

      result = rcsCommerceBackend.invokeApiSynchronous(request);

      RcsPhStaticStorage.set(staticKey, result);
      break;

    case 'dynamic-promotion-label':
      request.data = prepareQuery(`{${params.queryType}(
            ${params.queryProductSku}
            context: "web"
            ${params.queryProductViewMode}
            cart: {
              ${params.queryCartAttr}
              items: [
                ${params.cartInfo}
              ]
            }
          )
            ${params.queryBody}
          }`
        );
      result = rcsCommerceBackend.invokeApiSynchronous(request);
      break;

    default:
      console.log(`Placeholder ${placeholder} not supported for get_data.`);
      break;
  }

  return result;
}
