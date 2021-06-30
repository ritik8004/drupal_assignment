globalThis.rcsPhCommerceBackend = globalThis.rcsPhCommerceBackend || {};

globalThis.rcsPhCommerceBackend.getEntity = async function getEntity(langcode) {
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
        request.language = langcode;

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
