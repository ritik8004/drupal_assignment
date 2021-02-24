/* eslint-disable */

/**
 * @file
 * Javascript to load the BazaarVoice pixel map api.
 */


(function ($, Drupal, drupalSettings) {
    Drupal.behaviors.bvpixel_tracking = {
      attach(context, settings) {
        const orderFromStorage = localStorage.getItem('bvpixel_order_id');
        // Return if same order.
        // Else remove order from local storage if new order.
        if (orderFromStorage !== null) {
          if (orderFromStorage === drupalSettings.order_details.order_number) {
            return;
          }
        }
        localStorage.removeItem('bvpixel_order_id');
        const productsArray = [];
        const itemObj = {};
        for (const sku of Object.keys(drupalSettings.order_details.items)) {
          itemObj.productId = drupalSettings.order_details.items[sku].sku;
          itemObj.quantity = drupalSettings.order_details.items[sku].qtyOrdered;
          itemObj.name = drupalSettings.order_details.items[sku].title;
          itemObj.price = drupalSettings.order_details.items[sku].finalPrice;
          productsArray.push(itemObj);
        }
        const transactionData = {
          orderId: drupalSettings.order_details.order_number,
          city: drupalSettings.order_details.bv_extra_params.city,
          country: drupalSettings.order_details.bv_extra_params.country,
          email: drupalSettings.order_details.customer_email,
          locale: drupalSettings.order_details.bv_extra_params.locale,
          nickname: drupalSettings.order_details.bv_extra_params.nickname,
          userId: drupalSettings.order_details.bv_extra_params.customer_id,
          tax: drupalSettings.order_details.bv_extra_params.tax,
          shipping: drupalSettings.order_details.bv_extra_params.shipping,
          total: drupalSettings.order_details.totals.subtotal_incl_tax,
          currency: drupalSettings.order_details.bv_extra_params.currency,
          items: productsArray,
        };
        window.bvCallback = function (BV) {
          BV.pixel.trackTransaction(transactionData);
          // Store transaction info in local storage.
          localStorage.setItem('bvpixel_order_id', transactionData.orderId);
        };
      },
    };
  }(jQuery, Drupal, drupalSettings));
  