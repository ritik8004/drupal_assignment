/**
 * @file
 * Push SEO data to datalayer.
 */

(function (Drupal) {
  /**
   * Helper function to map the Global-e checkout data to datalayer.
   *
   * @param {object} data
   *   Checkout data Object.
   * @param {number} step
   *   Step number.
   *
   * @return {object}
   *   The checkout data mapping from global-e.
   */
  Drupal.getGlobaleGaCheckoutData = function (data, step) {
    var checkoutDataLayer = {
      language: drupalSettings.path.currentLanguage,
      country: data.details.CustomerDetails.ShippingAddress.ShippingCountryName,
      currency: data.details.CustomerCurrencyCode,
      pageType: "checkout delivery page",
      event: 'checkout',
      ecommerce: {
        currencyCode: data.details.CustomerCurrencyCode,
        checkout: {
          actionField: {
            step: step,
            action: "checkout"
          },
          products: []
        }
      },
      deliveryOption: step === 4 ? data.details.ShippingMethodName : undefined,
      deliveryType: step === 4 ? data.details.ShippingMethodType : undefined,
      deliveryArea: step === 4 ? data.details.CustomerDetails.ShippingAddress.ShippingCityRegion : undefined,
      deliveryCity: step === 4 ? data.details.CustomerDetails.ShippingAddress.ShippingCity : undefined,
      privilegeCustomer: "",
      privilegesCardNumber: "",
      productSKU: [],
      productStyleCode: [],
      cartTotalValue: "",
      cartItemsCount: "",
      cartItemsFlocktory: [],
      paymentOption: step === 4 ? data.details.PaymentMethods : undefined,
    };

    Object.entries(data.details.ProductInformation).forEach(function (productItem) {
      const product = productItem[1];
      const productGtm = {
        "quantity": product.Quantity,
        "name": product.ProductName,
        "id": product.ProductGroupCode,
        "price": product.ProductPrices.CustomerTransaction.CustomerTotalPrice,
        "brand": product.Brand,
        "category":product.Categories,
        "variant": product.SKU,
        "dimension2": "",
        "dimension4": "",
        "dimension3": "",
        "productOldPrice": product.ProductPrices.CustomerTransaction.CustomerTotalPrice
      };
      checkoutDataLayer.ecommerce.checkout.products.push(productGtm);

      checkoutDataLayer.productSKU.push(product.SKU);
      checkoutDataLayer.productStyleCode.push(product.ProductGroupCode);
      checkoutDataLayer.cartTotalValue = product.ProductPrices.CustomerTransactionInMerchantCurrency.CustomerTotalPriceInMerchantCurrency;

      const cartItemsFlocktory = {
        id: product.ProductGroupCode,
        price: product.CustomerDiscountedPriceInMerchantCurrency,
        count: "",
        title: product.ProductName,
        image: ""
      };
      checkoutDataLayer.cartItemsFlocktory.push(cartItemsFlocktory);
    });
    return checkoutDataLayer;
  };
})(Drupal);
