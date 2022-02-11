/**
 * @file
 * Sending order confirmation details to bazaarvoice.
 */

(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.bvpixel_tracking = {
    attach(context, settings) {
      const orderFromStorage = Drupal.getItemFromLocalStorage('bvpixel_order_id');
      // Return if same order.
      // Else remove order from local storage if new order.
      if (orderFromStorage !== null) {
        if (orderFromStorage === drupalSettings.order_details.order_number) {
          return;
        }
      }
      Drupal.removeItemFromLocalStorage('bvpixel_order_id');
      const productsArray = [];
      Object.keys(drupalSettings.order_details.items).forEach((sku) => {
        const itemObj = {};
        itemObj.productId = drupalSettings.order_details.items[sku].sku;
        itemObj.quantity = drupalSettings.order_details.items[sku].qtyOrdered;
        itemObj.name = drupalSettings.order_details.items[sku].title;
        itemObj.price = drupalSettings.order_details.items[sku].finalPrice.replace(/,/g, '');
        productsArray.push(itemObj);
      });
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
        // Sending order details through bv pixel function.
        BV.pixel.trackTransaction(transactionData);
        // Store transaction info in local storage.
        Drupal.addItemInLocalStorage('bvpixel_order_id', transactionData.orderId);
      };
    },
  };
})(jQuery, Drupal, drupalSettings);

