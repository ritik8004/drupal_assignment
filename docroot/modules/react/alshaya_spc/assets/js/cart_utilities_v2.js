(function ($, Drupal) {
  'use strict';

  // Check if the user is anonyous and if there is a cart.
  Drupal.alshayaSpc.isAnonmyousWithoutCart = function () {
    var cartData = Drupal.alshayaSpc.getCartData();
    if (typeof cartData === 'undefined' || typeof cartData.cart_id === 'undefined') {
console.log('isAnonmyousWithoutCart', true);
      return true;
    }
console.log('isAnonmyousWithoutCart', window.drupalSettings.user.uid === 0);
    return window.drupalSettings.user.uid === 0;
  };

  // Get cart ID from existing cart. Creates a new cart if necessary.
  // @todo remove proxy.
  Drupal.alshayaSpc.getCartId = function () {
    var settings = window.drupalSettings;
    var cartData = Drupal.alshayaSpc.getCartData();
    if (!cartData || typeof cartData.cart_id === 'undefined') {
      jQuery.ajax({
        async: false,
        timeout: settings.cart.timeouts['cart_create'] * 1000,
        url: '/proxy.php?url=' + encodeURI(settings.cart.url + '/' + settings.cart.store + '/rest/V1/guest-carts'),
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        error: function (jqXHR, textStatus, errorThrown) {
          console.log(textStatus, errorThrown);
        },
        success: function (response) {
          cartData = {
            cart_id: response,
          };
          localStorage.setItem('cart_data', JSON.stringify({ cart: cartData }));
        },
      });
    }

console.log('getCartId', cartData.cart_id);
    return cartData.cart_id;
  };

  // Update cart.
  Drupal.alshayaSpc.getCart = function () {
    var settings = drupalSettings;
    var cartId = Drupal.alshayaSpc.getCartId();
    var cartData = null;

    // @todo remove proxy.
    $.ajax({
      async: false,
      timeout: settings.cart.timeouts['cart_get'] * 1000,
      url: '/proxy.php?url=' + encodeURI(settings.cart.url + '/' + settings.cart.store + '/rest/V1/guest-carts/' + cartId + '/getCart'),
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.log('error', errorThrown);
      },
      success: function (response) {
        // console.log(response);
        cartData = Drupal.alshayaSpc.processCartData(response);
      },
    });
console.log('getCart', cartData);
    return cartData;
  };

  // Process cart data.
  Drupal.alshayaSpc.processCartData = function (data) {
    var cartData = {
      cart_id: Drupal.alshayaSpc.getCartId(), //@todo check why this is not the same as data.cart.id
      uid: (drupalSettings.user.uid) ? drupalSettings.user.uid : 0,
      langcode: $('html').attr('lang'),
      customer: data.cart.customer,
      coupon_code: '', //@todo where to find this? cart.totals.coupon_code
      appliedRules: data.cart.applied_rule_ids,
      items_qty: data.cart.items_qty,
      cart_total: data.totals.base_grand_total,
      minicart_total: data.totals.base_grand_total, //@todo confirm this
      surcharge: data.cart.extension_attributes.surcharge,
      response_message: null,
      in_stock: true,
      is_error: false,
      stale_cart: false, //@todo confirm this?
      totals: {
        subtotal_incl_tax: data.totals.subtotal_incl_tax,
        shipping_incl_tax: null,
        base_grand_total: data.totals.base_grand_total,
        base_grand_total_without_surcharge: data.totals.base_grand_total,
        discount_amount: data.totals.discount_amount,
        surcharge: 0,
      },
      items: [],
    };

    if (typeof data.shipping !== 'undefined') {
      // For click_n_collect we don't want to show this line at all.
      if (data.shipping.type !== 'click_and_collect') {
        cartData.totals.shipping_incl_tax = data.totals.shipping_incl_tax;
      }
    }

    if (typeof data.cart.extension_attributes.surcharge !== 'undefined' && data.cart.extension_attributes.surcharge.amount > 0 && data.cart.extension_attributes.surcharge.is_applied) {
      cartData.totals.surcharge = data.cart.extension_attributes.surcharge.amount;
      // We don't show surcharge amount on cart total and on mini cart.
      cartData.totals.base_grand_total_without_surcharge -= cartData.totals.surcharge;
      cartData.minicart_total -= cartData.totals.surcharge;
    }

    //@todo confirm this
    if (typeof data.response_message[1] !== 'undefined') {
      cartData.response_message = {
        status: data.response_message[1],
        msg: data.response_message[2],
      };
    }

    if (typeof data.cart.items !== 'undefined') {
      data.cart.items.forEach(function (item) {
        //todo check why item id is different from v1 and v2 for https://local.alshaya-bpae.com/en/buy-21st-century-c-1000mg-prolonged-release-110-tablets-red.html
          cartData.items[item.sku] = {
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
            cartData.items[item.item_id].error_msg = item.extension_attributes.error_message;
            cartData.is_error = true;
          }

          // This is to determine whether item to be shown free or not in cart.
          data.totals.items.forEach(function (totalItem) {
            // If total price of item matches discount, we mark as free.
            if (item.item_id === totalItem.item_id) {
              // Final price to use.
              // For the free gift the key 'price_incl_tax' is missing.
              if (typeof totalItem.price_incl_tax !== 'undefined') {
                cartData.items[item.sku].finalPrice = totalItem.price_incl_tax;
              } else {
                cartData.items[item.sku].finalPrice = totalItem.base_price;
              }

              // Free Item is only for free gift products which are having
              // price 0, rest all are free but still via different rules.
              if (totalItem.base_price === 0 &&
                typeof totalItem.extension_attributes !== 'undefined' &&
                typeof totalItem.amasty_promo !== 'undefined') {
                cartData.items[item.sku].freeItem = true;
              }
            }
          });

          // Get stock data.
          //@todo this ^
        },
      );
    }

    return cartData;
  };

})(jQuery, Drupal);

