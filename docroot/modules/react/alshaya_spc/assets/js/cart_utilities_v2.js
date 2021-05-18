(function ($, Drupal) {
  'use strict';

  // Get cart ID from existing cart. Creates a new cart if necessary.
  // @todo remove proxy.
  Drupal.alshayaSpc.getCartId = function () {
    var settings = drupalSettings;
    var cartData = Drupal.alshayaSpc.getCartData();
    if (!cartData || typeof cartData.cart_id === 'undefined') {
      jQuery.ajax({
        async: false,
        url: '/proxy.php?url=' + encodeURI(settings.cart.url + '/' + settings.cart.store + '/rest/V1/guest-carts'),
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        error: function (error) {
          console.log(error);
        },
        success: function (response) {
          cartData = {
            cart_id: response,
          };
          localStorage.setItem('cart_data', JSON.stringify({ cart: cartData }));
        },
      });
    }
    console.log(cartData.cart_id);
    return cartData.cart_id;
  };

  // Update cart.
  Drupal.alshayaSpc.fetchCartData = function () {
    var settings = drupalSettings;
    var cartId = Drupal.alshayaSpc.getCartId();
    var cartData = null;

    // @todo remove proxy.
    $.ajax({
      async: false,
      url: '/proxy.php?url=' + encodeURI(settings.cart.url + '/' + settings.cart.store + '/rest/V1/guest-carts/' + cartId + '/totals'),
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
      error: function (response) {
        console.log('error', response);
      },
      success: function (response) {
        console.log(response);
        cartData = Drupal.alshayaSpc.processCartData(response);
      },
    });

    return cartData;
  };

  // Process cart data.
  Drupal.alshayaSpc.processCartData = function (data) {
    var cartData = {
      cart_id: Drupal.alshayaSpc.getCartId(),
      uid: (drupalSettings.user.uid) ? drupalSettings.user.uid : 0,
      langcode: $('html').attr('lang'),
      customer: (data.customer) ? (data.customer) : null,
      coupon_code: null, //@todo where to find this?
      appliedRules: null, //@todo where to find this?
      items_qty: data.items_qty,
      cart_total: data.base_grand_total,
      minicart_total: data.base_grand_total, //@todo confirm this
      surcharge: null, //@todo confirm this?
      response_message: null,
      in_stock: true,
      is_error: false,
      stale_cart: false, //@todo confirm this?
      totals: {
        subtotal_incl_tax: data.subtotal_incl_tax,
        shipping_incl_tax: null,
        base_grand_total: data.base_grand_total,
        base_grand_total_without_surcharge: data.base_grand_total,
        discount_amount: data.discount_amount,
        surcharge: 0,
      },
      items: [],
    };

    if (typeof data.shipping !== 'undefined') {
      // For click_n_collect we don't want to show this line at all.
      if (data.shipping.type !== 'click_and_collect') {
        cartData.totals.shipping_incl_tax = data.shipping_incl_tax;
      }
    }

    if (typeof data.surcharge !== 'undefined' && data.surcharge.amount > 0 && data.surcharge.is_applied) {
      cartData.totals.surcharge = data.surcharge.amount;
      // We don't show surcharge amount on cart total and on mini cart.
      cartData.totals.base_grand_total_without_surcharge -= cartData.totals.surcharge;
      cartData.minicart_total -= cartData.totals.surcharge;
    }

    //@todo confirm this
    if (typeof data.response_message !== 'undefined') {
      cartData.response_message = {
        status: data.response_message[1],
        msg: data.response_message[2],
      };
    }

    if (typeof data.items !== 'undefined') {
      data.items.forEach(function (item) {
          //@todo Cart.php uses sku as keys but sku isn't available on the response.
          cartData.items[item.item_id] = {
            id: item.item_id,
            title: item.name,
            qty: item.qty,
            price: item.price,
            sku: '??????', //@todo sku not available
            freeItem: false,
            in_stock: null, //@todo get stock information
            stock: null, //@todo get stock information
          };

          if (typeof item.extension_attributes !== 'undefined' && typeof item.extension_attributes.error_message !== 'undefined') {
            cartData.items[item.item_id].error_msg = item.extension_attributes.error_message;
            cartData.is_error = TRUE;
          }

          // This is to determine whether item to be shown free or not in cart.
          //@todo this

        },
      );
    }

    return cartData;
  };

})(jQuery, Drupal);

