globalThis.rcsPhCommerceBackend = globalThis.rcsPhCommerceBackend || {};

globalThis.rcsPhCommerceBackend.getEntity = async function getEntity() {
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
        // @todo: Filter by the product URL.
        // @todo: Make a config for this query and pass it from the backend.
        request.data = JSON.stringify({
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

      break;

    default:
      console.log(
        `Entity type ${drupalSettings.rcsPage.type} not supported for get_entity.`
      );
      return result;
  }

  const response = await rcsCommerceBackend.invokeApi(request);
  if (response.results.length === 1) {
    result = response.results[0];
  }
  return result;
};
