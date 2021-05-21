/* eslint-env jquery */
import Axios from 'axios';

/**
 * Check if user is anonymous and without cart.
 *
 * @returns bool
 */
const isAnonymousUserWithoutCart = () => {
  const cartData = window.Drupal.alshayaSpc.getCartData();
  if (typeof cartData === 'undefined' || typeof cartData.cart_id === 'undefined') {
    return true;
  }
  return drupalSettings.user.uid === 0;
};

/**
 * Get the complete path for the Magento API.
 *
 * @param {string} path
 *  The API path.
 */
const i18nMagentoUrl = (path) => {
  let url = `${window.drupalSettings.cart.url}/${window.drupalSettings.cart.store}${path}`;
  url = `/proxy.php?url=${url}`; // @todo remove this when CORS is enabled on Magento API
  return url;
};

/**
 * Make an AJAX call to Magento API.
 *
 * @param {string} url
 *   The url to send the request to.
 * @param {string} method
 *   The request method.
 * @param {object} data
 *   The object to send for POST request.
 *
 * @returns {Promise}
 *   Returns a promise object.
 */
const callMagentoApi = (url, method, data) => {
  const params = {
    url: i18nMagentoUrl(url),
    method,
    headers: {
      'Content-Type': 'application/json',
    },
  };

  if (typeof data !== 'undefined' && Object.keys(data).length > 0) {
    params.data = data;
  }

  return Axios(params);
};

/**
 * Calls the update cart API.
 *
 * @param {object} data
 *   The data object to send in the API call.
 *
 * @returns {Promise}
 *   A promise object.
 */
const updateCart = (data) => {
  const def = $.Deferred();
  window.commerceBackend.getCartId()
    .then((cartId) => {
      const itemData = {
        cartItem: {
          sku: data.variant_sku,
          qty: data.quantity,
          quote_id: cartId,
        },
      };
      return callMagentoApi(`/rest/V1/guest-carts/${cartId}/items`, 'POST', itemData);
    })
    .then(
      () => window.commerceBackend.getCart(),
    )
    .then((cartData) => {
      if (typeof cartData !== 'undefined') {
        def.resolve(cartData);
      }
    });

  return def.promise();
};

export {
  isAnonymousUserWithoutCart,
  callMagentoApi,
  updateCart,
};
