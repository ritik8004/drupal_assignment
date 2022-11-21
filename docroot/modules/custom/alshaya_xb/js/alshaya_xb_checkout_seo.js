/**
 * @file
 * Push SEO data to datalayer.
 */

(function (Drupal) {
  /**
   * Pushes Global-e data to Datalayer.
   *
   * @param {object} geData
   *   The Global-e data.
   * @param {integer} step.
   *   The step number.
   */
  Drupal.alshayaXbCheckoutGaPush = function (geData, step) {
    try {
      if (typeof geData.details.PaymentMethods !== 'undefined' && geData.details.PaymentMethods !== null) {
        // Populate drupal settings with details from GE data.
        drupalSettings.payment_methods['global-e'] = geData.details.PaymentMethods[0].PaymentMethodTypeName;
      }
      Drupal.alshayaSeoSpc.checkoutEvent(Drupal.mapGlobaleCheckoutData(geData), step);
    }
    catch (error) {
      Drupal.logJavascriptError("Alshaya XB Checkout", error, GTM_CONSTANTS.CHECKOUT_ERRORS);
    }
  }

  /**
   * Helper function to map the Global-e checkout data to cart data.
   *
   * @param {object} geData
   *   Global-e checkout data.
   *
   * @return {object}
   *   The cart data object.
   */
  Drupal.mapGlobaleCheckoutData = function (geData) {
    let productGtm = [];
    let cartItemsCount = 0;
    if (geData.details.ProductInformation) {
      Object.entries(geData.details.ProductInformation).forEach(function (productItem) {
        const product = productItem[1];
        const productGtmData = {
          "item_id": product.CartItemId,
          "sku": product.SKU,
          "qty": product.Quantity,
          "name": product.ProductName,
          "price": product.ProductPrices.CustomerTransactionInMerchantCurrency.CustomerTotalPriceInMerchantCurrency,
          "finalPrice": product.ProductPrices.CustomerTransactionInMerchantCurrency.CustomerTotalPriceInMerchantCurrency,
        };
        cartItemsCount = parseInt(product.Quantity) + cartItemsCount;
        productGtm.push(productGtmData);
      });
    }

    return {
      "cart_id": window.commerceBackend.getCartId(),
      "uid": drupalSettings.user.uid,
      "items_qty": cartItemsCount,
      "cart_total": geData.details.OrderPrices.CustomerTransactionInMerchantCurrency.CustomerTotalProductsPriceInMerchantCurrency,
      "minicart_total": geData.details.OrderPrices.CustomerTransactionInMerchantCurrency.CustomerTotalProductsPriceInMerchantCurrency,
      "surcharge": {
        "amount": geData.details.OrderPrices.CustomerTransactionInMerchantCurrency.Fees.CustomerRemoteAreaSurchargeFeeInMerchantCurrency,
        "is_applied": (geData.details.OrderPrices.CustomerTransactionInMerchantCurrency.Fees.CustomerRemoteAreaSurchargeFeeInMerchantCurrency > 0) ? true : false
      },
      "shipping": {
        "type": geData.details.ShippingMethodType,
        "methods": geData.details.ShippingMethodName,
      },
      "payment": {
        "method": geData.OrderPaymentMethods
      },
      "totals": {
        "subtotal_incl_tax": geData.details.OrderPrices.CustomerTransactionInMerchantCurrency.CustomerTotalProductsPriceInMerchantCurrency,
        "base_grand_total": geData.details.OrderPrices.CustomerTransactionInMerchantCurrency.CustomerTotalProductsPriceInMerchantCurrency,
        "base_grand_total_without_surcharge": geData.details.OrderPrices.CustomerTransactionInMerchantCurrency.CustomerTotalPriceInMerchantCurrency,
        "discount_amount": geData.details.OrderPrices.CustomerTransactionInMerchantCurrency.CustomerTotalDiscountedProductsPriceInMerchantCurrency,
        "surcharge": 0, // @todo we need to check whether global-e has this field or not
        "shipping_incl_tax": null // @todo we need to check whether global-e has this field or not
      },
      "items": productGtm,
    };
  };
})(Drupal);
