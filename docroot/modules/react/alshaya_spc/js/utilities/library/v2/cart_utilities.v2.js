/* eslint-env jquery */
import Axios from 'axios';

drupalSettings = window.drupalSettings;
window.commerceBackend = {};

/**
 * Get the complete path for the Magento API.
 *
 * @param {string} path
 *  The API path.
 */
const i18nMagentoUrl = (path) => {
  const cartUrl = drupalSettings.cart.url;
  const cartStore = drupalSettings.cart.store;
  let url = `${cartUrl}/${cartStore}${path}`;
  // @todo remove this when CORS is enabled on Magento API
  url = `/proxy.php?url=${url}`;
  return url;
};

/**
 * Check if user is anonymous and without cart.
 *
 * @returns bool
 */
window.commerceBackend.isAnonymousUserWithoutCart = () => {
  const cartData = window.Drupal.alshayaSpc.getCartData();
  if (typeof cartData === 'undefined' || typeof cartData.cart_id === 'undefined') {
    return true;
  }
  return drupalSettings.user.uid === 0;
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
 * Calls the cart get API.
 */
window.commerceBackend.getCart = () => {
  const def = $.Deferred();
  window.commerceBackend.getCartId()
    .then((cartId) => {
      callMagentoApi(`/rest/V1/guest-carts/${cartId}/getCart`, 'GET', {})
        .then((response) => {
          if (typeof response.data !== 'undefined') {
            def.resolve(response.data);
          }
        });
    });

  return def.promise();
};

/**
 * Add items to cart.
 *
 * @param {object} data
 *   The object to send for POST request.
 */
window.commerceBackend.addToCart = (data) => {
  const def = $.Deferred();
  window.commerceBackend.getCartId()
    .then((cartId) => {
      callMagentoApi(`/rest/V1/guest-carts/${cartId}/items`, 'POST', data)
        .then((response) => {
          if (typeof response.data !== 'undefined') {
            def.resolve(response.data);
          }
        });
    });

  return def.promise();
};

/**
 * Get cart data for checkout.
 */
window.commerceBackend.getCartForCheckout = () => {
  // @todo Implement getCartForCheckout().
};

/**
 * Calls the cart restore API.
 */
window.commerceBackend.restoreCart = () => {
  // @todo Implement restoreCart().
};

/**
 * Calls the cart update API.
 *
 * @param {object} data
 *   The data object to send in the API call.
 */
window.commerceBackend.updateCart = (data) => {
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

      return window.commerceBackend.addToCart(itemData);
    })
    .then(
      () => window.commerceBackend.getCart(),
    )
    .then((cartData) => {
      if (typeof cartData !== 'undefined') {
        const processedData = window.commerceBackend.processCartData(cartData);
        def.resolve(processedData);
      }
    });

  return def.promise();
};

/**
 * Gets the cart ID for existing cart.
 * Creates a new cart if necessary.
 * Stores cart Id in the local storage.
 */
window.commerceBackend.getCartId = () => {
  const def = $.Deferred();
  let cartData = window.Drupal.alshayaSpc.getCartData();
  if (cartData && typeof cartData.cart_id !== 'undefined') {
    def.resolve(cartData.cart_id);
  } else {
    callMagentoApi('/rest/V1/guest-carts', 'POST', {})
      .then((response) => {
        if (typeof response.data === 'string' || typeof response.data === 'number') {
          cartData = {
            cart_id: response.data,
          };
          localStorage.setItem('cart_data', JSON.stringify({ cart: cartData }));
          def.resolve(cartData.cart_id);
        }
      });
  }
  return def.promise();
};

/**
 * Transforms cart data to match the data structure from middleware.
 *
 * @param {object} cartData
 *   The cart data object.
 */
window.commerceBackend.processCartData = (cartData) => {
  if (typeof cartData === 'undefined' || typeof cartData.cart === 'undefined') {
    return;
  }

  const data = {
    cart_id: window.commerceBackend.getCartId(),
    uid: (drupalSettings.user.uid) ? drupalSettings.user.uid : 0,
    langcode: $('html').attr('lang'),
    customer: cartData.cart.customer,
    coupon_code: '', // @todo where to find this? cart.totals.coupon_code
    appliedRules: cartData.cart.applied_rule_ids,
    items_qty: cartData.cart.items_qty,
    cart_total: cartData.totals.base_grand_total,
    minicart_total: cartData.totals.base_grand_total, // @todo confirm this
    surcharge: cartData.cart.extension_attributes.surcharge,
    response_message: null,
    in_stock: true,
    is_error: false,
    stale_cart: false, // @todo confirm this?
    totals: {
      subtotal_incl_tax: cartData.totals.subtotal_incl_tax,
      shipping_incl_tax: null,
      base_grand_total: cartData.totals.base_grand_total,
      base_grand_total_without_surcharge: cartData.totals.base_grand_total,
      discount_amount: cartData.totals.discount_amount,
      surcharge: 0,
    },
    items: [],
  };

  if (typeof cartData.shipping !== 'undefined') {
    // For click_n_collect we don't want to show this line at all.
    if (cartData.shipping.type !== 'click_and_collect') {
      data.totals.shipping_incl_tax = cartData.totals.shipping_incl_tax;
    }
  }

  if (typeof cartData.cart.extension_attributes.surcharge !== 'undefined' && cartData.cart.extension_attributes.surcharge.amount > 0 && cartData.cart.extension_attributes.surcharge.is_applied) {
    data.totals.surcharge = cartData.cart.extension_attributes.surcharge.amount;
    // We don't show surcharge amount on cart total and on mini cart.
    data.totals.base_grand_total_without_surcharge -= data.totals.surcharge;
    data.minicart_total -= data.totals.surcharge;
  }

  // @todo confirm this
  if (typeof cartData.response_message[1] !== 'undefined') {
    data.response_message = {
      status: cartData.response_message[1],
      msg: cartData.response_message[2],
    };
  }

  if (typeof cartData.cart.items !== 'undefined') {
    cartData.cart.items.forEach((item) => {
      // @todo check why item id is different from v1 and v2 for
      // https://local.alshaya-bpae.com/en/buy-21st-century-c-1000mg-prolonged-release-110-tablets-red.html
      data.items[item.sku] = {
        id: item.item_id,
        title: item.name,
        qty: item.qty,
        price: item.price,
        sku: item.sku,
        freeItem: false,
        finalPrice: item.price,
        in_stock: true, // @todo get stock information
        stock: 99999, // @todo get stock information
      };

      if (typeof item.extension_attributes !== 'undefined' && typeof item.extension_attributes.error_message !== 'undefined') {
        data.items[item.item_id].error_msg = item.extension_attributes.error_message;
        data.is_error = true;
      }

      // This is to determine whether item to be shown free or not in cart.
      cartData.totals.items.forEach((totalItem) => {
        // If total price of item matches discount, we mark as free.
        if (item.item_id === totalItem.item_id) {
          // Final price to use.
          // For the free gift the key 'price_incl_tax' is missing.
          if (typeof totalItem.price_incl_tax !== 'undefined') {
            data.items[item.sku].finalPrice = totalItem.price_incl_tax;
          } else {
            data.items[item.sku].finalPrice = totalItem.base_price;
          }

          // Free Item is only for free gift products which are having
          // price 0, rest all are free but still via different rules.
          if (totalItem.base_price === 0 && typeof totalItem.extension_attributes !== 'undefined' && typeof totalItem.amasty_promo !== 'undefined') {
            data.items[item.sku].freeItem = true;
          }
        }
      });

      // @todo Get stock data.
    });
  }

  const processedData = { data };

  // eslint-disable-next-line consistent-return
  return processedData;
};
