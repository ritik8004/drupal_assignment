(function ($, drupalSettings) {

  const pageEntityType = drupalSettings.pageEntityType;

  /**
   * Gets the data to send in the request.
   *
   * @param {string} entityType
   *   The entity type of the object.
   */
  function getRequestData(entityType) {
    switch (entityType) {
      case 'product':
        // @todo: Filter by the product URL.
        return JSON.stringify({
          query: `{products(filter: {sku: {eq: "E0110"}}) {
            total_count
            items {
                sku
                id
                name
                meta_title
                special_price
                url_key
                url_path
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
            }
          }}`
        });

      default:
          return null;
    }
  }

  /**
   * Processes the response before sending it forward for markup replacement.
   *
   * @param {object} response
   *   The response object.
   */
  function preprocessResponse(response) {
    const data = response.data.products.total_count
      ? response.data.products.items[0]
      : null;

    if (!data) {
      alert('No item in response');
      return;
    }

    window.rcs.replaceEntityTokens(pageEntityType, data);
  }

  $(document).ready(function () {
    var url = null;
    var data = null;
    // const langcode = drupalSettings.path.currentLanguage;
    // const pagePath = window.location.pathname.replace('/' + langcode + '', '');

    switch (pageEntityType) {
      case 'product':
        url = drupalSettings.rcs.backend_url + '/graphql';
        data = getRequestData('product');
        break;

      default:
        break;
    }

    window.rcsBackend.fetchData(
      url,
      "POST",
      { data: data, headers: [["Content-Type", "application/json"]] },
      preprocessResponse,
    );
  });

})(jQuery, drupalSettings);
