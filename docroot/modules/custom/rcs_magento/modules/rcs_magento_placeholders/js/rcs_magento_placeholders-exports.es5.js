/**
 * Handle 404 case on initial request.
 *
 * @param {object} request
 *   The request object.
 * @param {string} urlKey
 *   The url key.
 */
async function handleNoItemsInResponse(request, urlKey) {
  request.data = prepareQuery(rcsPhGraphqlQuery.urlResolver.query, rcsPhGraphqlQuery.urlResolver.variables);

  let response = await rcsCommerceBackend.invokeApi(request);
  let rcs404 = `${drupalSettings.rcs['404Page']}?referer=${globalThis.rcsWindowLocation().pathname}`;

  if (response.data.urlResolver === null
    || response.data.urlResolver.redirectCode == 404) {
    // Hide body so that placeholders are not visible.
    document.body.classList.add('hidden');
    return rcsRedirectToPage(rcs404);
  }

  if ([301, 302].includes(response.data.urlResolver.redirectCode)) {
    let relative_url = response.data.urlResolver.relative_url.startsWith('/')
      ? response.data.urlResolver.relative_url
      : `/${drupalSettings.path.currentLanguage}/${response.data.urlResolver.relative_url}`;
    return rcsRedirectToPage(relative_url);
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
function prepareQuery(query, variables) {
  let graphqlQuery = null;
  const data = {
    query: query,
    variables: variables,
  };

  // Remove extra enclosing {}.
  data.query = data.query.slice(1, -1);
  // Encode to valid uri format.
  data.query = encodeURIComponent(global.rcsQueryCompressor(data.query));

  if (typeof data.variables !== 'undefined') {
    data.variables = JSON.stringify(data.variables);
    graphqlQuery = `query=${data.query}&variables=${data.variables}`;
  }
  else {
    graphqlQuery = `query=${data.query}`;
  }

  return graphqlQuery;
}

exports.getEntity = async function getEntity(langcode) {
  const pageType = globalThis.rcsPhGetPageType();
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
      request.data = prepareQuery(rcsPhGraphqlQuery.pdp_product.query, rcsPhGraphqlQuery.pdp_product.variables);
      // Fetch response.
      response = await rcsCommerceBackend.invokeApi(request);

      if (response && response.data.products.total_count) {
        result = response.data.products.items[0];
        result.context = 'pdp';
        // Store product data in static storage.
        globalThis.RcsPhStaticStorage.set('product_data_' + result.sku, result);
        // Set product options data to static storage.
        globalThis.RcsPhStaticStorage.set('product_options', {data: {customAttributeMetadata: response.data.customAttributeMetadata}});
      }
      else {
        await handleNoItemsInResponse(request, urlKey);
      }
      break;

    case 'category':
      // Build query.
      request.data = prepareQuery(rcsPhGraphqlQuery.categories.query, rcsPhGraphqlQuery.categories.variables);

      // Fetch response.
      response = await rcsCommerceBackend.invokeApi(request);
      if (response && response.data.categories.total_count) {
        result = response.data.categories.items[0];
        var currentPath = window.location.href;
        // The condition "currentPath.indexOf('/view-all')" is to ensure that
        // this should execute only for view all page,
        // "result.display_view_all !== 1" this one is to check whether we have
        // the view_all field set to true or not.
        if ((currentPath.indexOf('/view-all') != -1) && (result.display_view_all !== 1)) {
          await handleNoItemsInResponse(request, urlKey);
        }
      }
      else {
        await handleNoItemsInResponse(request, urlKey);
      }
      break;

    case 'promotion':
      // Build query.
      request.data = prepareQuery(rcsPhGraphqlQuery.promotions.query, rcsPhGraphqlQuery.promotions.variables);

      // Fetch response.
      response = await rcsCommerceBackend.invokeApi(request);
      if (response.data.promotionUrlResolver) {
        result = response.data.promotionUrlResolver;
      }

      // Check if title is null and call UrlResolver for redirection.
      if(result.title == null) {
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

exports.getData = async function getData(
  placeholder,
  params,
  entity,
  langcode,
  markup,
  loaderOnUpdates = false,
  authorizationToken = null
) {
  const request = {
    uri: '/graphql',
    method: 'GET',
    headers: [
      ['Content-Type', 'application/json'],
      ['Store', drupalSettings.rcs.commerceBackend.store],
    ],
    language: langcode,
  };

  if (authorizationToken) {
    request.headers.push(['Authorization', authorizationToken]);
  }

  request.rcsType = placeholder;

  let response = null;
  let result = null;
  let context = null;

  switch (placeholder) {
    // No need to fetch anything. The markup will be there in the document body.
    // Just return empty string so that render() function gets called later.
    case 'delivery-option':
      result = '';
      break;

    case 'navigation_menu':
      // Early return if the root category is undefined.
      if (typeof params.category_id === 'undefined') {
        return null;
      }

      const staticKey = placeholder + '_data';
      const staticNavigationData = globalThis.RcsPhStaticStorage.get(staticKey);
      // Return the data from static storage if available.
      if (staticNavigationData !== null) {
        return staticNavigationData;
      }

      // Prepare request parameters.
      // Fetch categories for navigation menu using categories api.
      request.data = prepareQuery(rcsPhGraphqlQuery.navigationMenu.query, rcsPhGraphqlQuery.navigationMenu.variables);

      response = await rcsCommerceBackend.invokeApi(request);
      // Get exact data from response.
      if (response !== null
        && Array.isArray(response.data.categories.items)
        && response.data.categories.items.length > 0
      ) {
        // Get children for root category.
        result = response.data.categories.items[0].children;
        // Store category data in static storage.
        globalThis.RcsPhStaticStorage.set(placeholder + '_data', result);
      }
      break;

    case 'field_magazine_shop_the_story':
      let productQueryVariables = rcsPhGraphqlQuery.magazine_shop_the_story.variables;
      productQueryVariables.skus = JSON.parse(params.skus);
      request.data = prepareQuery(rcsPhGraphqlQuery.magazine_shop_the_story.query, productQueryVariables);

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
      let productLabelVariables = rcsPhGraphqlQuery.product_labels.variables;
      productLabelVariables.productIds = params.productIds;
      request.data = prepareQuery(rcsPhGraphqlQuery.product_labels.query, productLabelVariables);

      response = await rcsCommerceBackend.invokeApi(request);
      result = response.data.amLabelProvider;
      break;

    case 'product-recommendation':
      let prVariables = rcsPhGraphqlQuery.single_complete_product_by_sku.variables;
      prVariables.sku = params.sku;
      // @TODO Review this query to use only fields that are required for the display.
      request.data = prepareQuery(rcsPhGraphqlQuery.single_complete_product_by_sku.query, prVariables);

      response = await rcsCommerceBackend.invokeApi(request);
      result = response.data.products.items[0];
      context = 'modal';
      result.context = context;
      globalThis.RcsPhStaticStorage.set('product_data_' + result.sku, result);

      break;

    // Get the product data for the given sku.
    case 'product_by_sku':
      // Build query.
      let productBySkuVariables = rcsPhGraphqlQuery.product_by_sku.variables;
      productBySkuVariables.sku = params.sku;
      request.data = prepareQuery(rcsPhGraphqlQuery.product_by_sku.query, productBySkuVariables);

      response = await rcsCommerceBackend.invokeApi(request);
      result = response.data.products.items[0];
      break;

    case 'category_parents_by_path':
      let productCategoryParentVariables = rcsPhGraphqlQuery.category_parents_by_path.variables;
      productCategoryParentVariables.urlPath = params.urlPath;
      request.data = prepareQuery(rcsPhGraphqlQuery.category_parents_by_path.query, productCategoryParentVariables);
      response = await rcsCommerceBackend.invokeApi(request);
      result = response.data.categories.items[0];
      break;

    case 'category_children_by_path':
      let productCategoryChildrenVariables = rcsPhGraphqlQuery.category_children_by_path.variables;
      productCategoryChildrenVariables.urlPath = params.urlPath;
      request.data = prepareQuery(rcsPhGraphqlQuery.category_children_by_path.query, productCategoryChildrenVariables);
      response = await rcsCommerceBackend.invokeApi(request);
      result = response.data.categories.items[0];
      break;

    case 'cart_items_stock':
      let cartItemsStockVariables = rcsPhGraphqlQuery.cart_items_stock.variables;
      cartItemsStockVariables.cartId = params.cartId;
      request.data = prepareQuery(rcsPhGraphqlQuery.cart_items_stock.query, cartItemsStockVariables);

      response = await rcsCommerceBackend.invokeApi(request);
      result = response.data;
      break;

    case 'products-in-style':
      let variables = rcsPhGraphqlQuery.styled_products.variables;
      variables.styleCode = params.styleCode.toString();

      request.data = prepareQuery(rcsPhGraphqlQuery.styled_products.query, variables);
      response = await rcsCommerceBackend.invokeApi(request);
      result = response.data.products.items;
      break;

    // Get the product data for the given sku.
    case 'single_product_by_sku':
      // Build query.
      let singleProductQueryVariables = rcsPhGraphqlQuery.single_product_by_sku.variables;
      singleProductQueryVariables.sku = params.sku;
      request.data = prepareQuery(rcsPhGraphqlQuery.single_product_by_sku.query, singleProductQueryVariables);
      result = rcsCommerceBackend.invokeApi(request);
      break;

    case 'related-products':
      // Build query.
      let relatedListVariables = rcsPhGraphqlQuery.related_products.variables;
      relatedListVariables.sku = params.sku;
      request.data = prepareQuery(rcsPhGraphqlQuery.related_products.query, relatedListVariables);
      response = await rcsCommerceBackend.invokeApi(request);
      result = response.data.products.items;
      break;

    case 'upsell-products':
      // Build query.
      let upsellListVariables = rcsPhGraphqlQuery.upsell_products.variables;
      upsellListVariables.sku = params.sku;
      request.data = prepareQuery(rcsPhGraphqlQuery.upsell_products.query, upsellListVariables);
      response = await rcsCommerceBackend.invokeApi(request);
      result = response.data.products.items;
      break;

    case 'crosssell-products':
      // Build query.
      let crosselListVariables = rcsPhGraphqlQuery.crosssell_products.variables;
      crosselListVariables.sku = params.sku;
      request.data = prepareQuery(rcsPhGraphqlQuery.crosssell_products.query, crosselListVariables);
      response = await rcsCommerceBackend.invokeApi(request);
      result = response.data.products.items;
      break;

    case 'product_additional_attributes':
      let additionalAttributesVariables = rcsPhGraphqlQuery.product_additional_attributes.variables;
      additionalAttributesVariables.sku = params.sku;
      additionalAttributesVariables.attributes = params.attributes;
      request.data = prepareQuery(rcsPhGraphqlQuery.product_additional_attributes.query, additionalAttributesVariables);
      result = rcsCommerceBackend.invokeApi(request);
      break;

    default:
      console.log(`Placeholder ${placeholder} not supported by default for get_data.`);

      const eventData = {
        request,
        promises: [],
        extraData: {
          params,
          placeholder,
        },
      }
      // Allow the custom code to initiate other AJAX requests in parallel
      // and make the rendering blocked till all of them are finished.
      RcsEventManager.fire('invokingApi', eventData);
      if (eventData.promises.length) {
        return Promise.all(eventData.promises);
      }
  }

  if ((result && result !== null)) {
    // Display loader.
    if (loaderOnUpdates) {
      RcsEventManager.fire('startLoader');
    }

    // Creating custom event to perform extra operation and update the result
    // object.
    const updateResult = RcsEventManager.fire('rcsUpdateResults', {
      detail: {
        result: result,
        params: params,
        placeholder: placeholder,
        context,
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
      let variables = rcsPhGraphqlQuery.styled_products.variables;
      variables.styleCode = params.styleCode;

      request.data = prepareQuery(rcsPhGraphqlQuery.styled_products.query, variables);
      response = rcsCommerceBackend.invokeApiSynchronous(request);
      result = response.data.products.items;
      break;

    // Get the product data for the given sku.
    case 'multiple_products_by_sku':
      // Build query.
      let multipleProductQueryVariables = rcsPhGraphqlQuery.multiple_products_by_sku.variables;
      multipleProductQueryVariables.skus = params.sku;

      request.data = prepareQuery(rcsPhGraphqlQuery.multiple_products_by_sku.query, multipleProductQueryVariables);

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
      const staticKey = `product_options`;
      const staticOption = globalThis.RcsPhStaticStorage.get(staticKey);

      if (staticOption !== null) {
        return staticOption;
      }

      request.data = prepareQuery(rcsPhGraphqlQuery.product_options.query, rcsPhGraphqlQuery.product_options.variables);

      result = rcsCommerceBackend.invokeApiSynchronous(request);

      globalThis.RcsPhStaticStorage.set(staticKey, result);
      break;

    case 'dynamic-promotion-label':
      request.data = prepareQuery(`{
        {${params.queryType}(
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
        }
      }`);
      result = rcsCommerceBackend.invokeApiSynchronous(request);
      break;

    default:
      console.log(`Placeholder ${placeholder} not supported for get_data.`);
      break;
  }

  return result;
}
