(function ($, Drupal) {
  'use strict';

  Drupal.alshayaSpc = Drupal.alshayaSpc || {};

  /**
   * Check if user is anonymous and without cart.
   *
   * @returns bool
   */
  Drupal.alshayaSpc.isAnonymousUserWithoutCart = function () {
    var cartData = Drupal.alshayaSpc.getCartData();
    if (typeof cartData === 'undefined' || typeof cartData.cart_id === 'undefined') {
      return true;
    }
    return window.drupalSettings.user.uid === 0;
  };

  /**
   * Get the complete path for the Magento API.
   *
   * @param {string} path
   *  The API path.
   */
  const i18nMagentoUrl = function (path) {
    const cartUrl = window.drupalSettings.cart.url;
    const cartStore = window.drupalSettings.cart.store;
    var url = cartUrl + '/' + cartStore + path;
    //@todo remove this when CORS is enabled on Magento API
    url = '/proxy.php?url=' + encodeURI(url);
    return url;
  }

  /**
   * Make an AJAX call to Magento API.
   *
   * @param {string} url
   *   The url to send the request to.
   * @param {string} method
   *   The request method.
   * @param {object} data
   *   The object to send for POST request.
   * @param {integer} timeout
   *   The time in seconds.
   */
  const callMagentoApi = function (url, method, data, timeout) {
    const ajaxCallParams = {
      url: i18nMagentoUrl(url),
      method: method,
      timeout: timeout,
      headers: {
        'Content-Type': 'application/json',
      }
    }

    if (typeof data !== 'undefined') {
      ajaxCallParams.data = data;
    }

    return $.ajax(ajaxCallParams)
      .then(
        function (response) {
          // Return the data in the expected format.
          return {data: response};
        },
        function (error) {
          // Return a promise with the error message so that it can be catched.
          return new Promise(function (resolve, reject) {
            reject({message: 'Request failed with status code ' + error.status});
          });
        }
      );
  }

  /**
   * Calls the cart get API.
   */
  Drupal.alshayaSpc.getCart = function () {
    Drupal.alshayaSpc.getCartId()
      .then(function(cartId){
        const timeout = window.drupalSettings.cart.timeouts['cart_get'] * 1000;
        return callMagentoApi('/rest/V1/guest-carts/' + cartId + '/getCart', 'GET', {}, timeout);
      });
  }

  /**
   * Add items to cart.
   *
   * @param {object} data
   *   The object to send for POST request.
   */
  Drupal.alshayaSpc.addToCart = function (data) {
    Drupal.alshayaSpc.getCartId()
      .then(function (cartId) {
        const timeout = window.drupalSettings.cart.timeouts['cart_update'] * 1000;
        return callMagentoApi('/rest/V1/guest-carts/' + cartId + '/items', 'POST', data, timeout);
      });
  }

  /**
   * Get cart data for checkout.
   */
  Drupal.alshayaSpc.getCartForCheckout = function () {
    // @todo Implement getCartForCheckout().
    alert('Implement getCartForCheckout');
  }

  /**
   * Calls the cart restore API.
   */
  Drupal.alshayaSpc.restoreCart = function () {
    // @todo Implement restoreCart().
    alert('Implement getCartForCheckout');
  }

  /**
   * Calls the cart update API.
   *
   * @param {object} data
   *   The data object to send in the API call.
   */
  Drupal.alshayaSpc.updateCart = function (data) {
    var def = $.Deferred();
    Drupal.alshayaSpc.getCartId()
      .then(function (cartId) {
        console.log(cartId);
        console.log(data);
        data = {
          cartItem: {
            sku: data.variant_sku,
            qty: data.quantity,
            quote_id: cartId,
          }
        };
        console.log(data);
        return Drupal.alshayaSpc.addToCart(data);
      })
      .then(function () {
        return Drupal.alshayaSpc.getCart();
      })
      .done(function (cartData) {
        console.log(cartData);
        if (typeof cartData !== 'undefined') {
          def.resolve(Drupal.alshayaSpc.processCartData(cartData));
        }
      });

    return def.promise();
  }

  /**
   * Gets the cart ID for existing cart.
   * Creates a new cart if necessary.
   * Stores cart Id in the local storage.
   */
  Drupal.alshayaSpc.getCartId = function () {
    var def = $.Deferred();
    var cartData = Drupal.alshayaSpc.getCartData();
    const timeout = window.drupalSettings.cart.timeouts['cart_create'] * 1000;

    if (cartData && typeof cartData.cart_id !== 'undefined') {
      def.resolve(cartData.cart_id);
    } else {
      callMagentoApi('/rest/V1/guest-carts', 'POST', {}, timeout)
        .then(
          function (response) {
            if (typeof response.data === 'string' || typeof response.data === 'number') {
              cartData = {
                cart_id: response.data,
              };
              localStorage.setItem('cart_data', JSON.stringify({cart: cartData}));
              console.log('getCartId', cartData.cart_id);
              def.resolve(cartData.cart_id);
            }
          }
        );
    }
    return def.promise();
  }

  /**
   * Transforms cart data to match the data structure from middleware.
   *
   * @param {object} cartData
   *   The cart data object.
   */
  Drupal.alshayaSpc.processCartData = function (cartData) {
    if (typeof cartData === 'undefined') {
      return;
    }

    var data = {
      cart_id: Drupal.alshayaSpc.getCartId(), //@todo check why this is not the same as cartData.cart.id
      uid: (drupalSettings.user.uid) ? drupalSettings.user.uid : 0,
      langcode: $('html').attr('lang'),
      customer: cartData.cart.customer,
      coupon_code: '', //@todo where to find this? cart.totals.coupon_code
      appliedRules: cartData.cart.applied_rule_ids,
      items_qty: cartData.cart.items_qty,
      cart_total: cartData.totals.base_grand_total,
      minicart_total: cartData.totals.base_grand_total, //@todo confirm this
      surcharge: cartData.cart.extension_attributes.surcharge,
      response_message: null,
      in_stock: true,
      is_error: false,
      stale_cart: false, //@todo confirm this?
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

    //@todo confirm this
    if (typeof cartData.response_message[1] !== 'undefined') {
      data.response_message = {
        status: cartData.response_message[1],
        msg: cartData.response_message[2],
      };
    }

    if (typeof cartData.cart.items !== 'undefined') {
      cartData.cart.items.forEach(function (item) {
        //todo check why item id is different from v1 and v2 for https://local.alshaya-bpae.com/en/buy-21st-century-c-1000mg-prolonged-release-110-tablets-red.html
        data.items[item.sku] = {
          id: item.item_id,
          title: item.name,
          qty: item.qty,
          price: item.price,
          sku: item.sku,
          freeItem: false,
          finalPrice: item.price,
          in_stock: true, //@todo get stock information
          stock: 99999, //@todo get stock information
        };

        if (typeof item.extension_attributes !== 'undefined' && typeof item.extension_attributes.error_message !== 'undefined') {
          data.items[item.item_id].error_msg = item.extension_attributes.error_message;
          data.is_error = true;
        }

        // This is to determine whether item to be shown free or not in cart.
        cartData.totals.items.forEach(function (totalItem) {
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
            if (totalItem.base_price === 0 &&
              typeof totalItem.extension_attributes !== 'undefined' &&
              typeof totalItem.amasty_promo !== 'undefined') {
              data.items[item.sku].freeItem = true;
            }
          }
        });

        //@todo Get stock data.

      });
    }

    return data;
  };

})(jQuery, Drupal);
