/**
 * @file
 * Push SEO data to datalayer.
 */

(function (Drupal, dataLayer) {

  /**
   * Checks if all mandatory address fields are filled.
   *
   * @param {object} geData
   *   The Global-e data.
   *
   * @return {boolean}
   *   Returns true if all mandatory address are filled.
   */
  Drupal.isXbAddressAvailable = function (geData) {
    var shippingAddress = geData.details.CustomerDetails.ShippingAddress;
    var mandatoryAddressFields = [
      'ShippingFirstName',
      'ShippingLastName',
      'ShippingAddress1',
      'ShippingCity',
      'ShippingZipCode',
      'ShippingPhoneNumber'
    ];
    for (let i =0; i < mandatoryAddressFields.length; i++) {
      if (!Drupal.hasValue(shippingAddress[mandatoryAddressFields[i]])) {
        return false;
      }
    }
    return true;
  };

  /**
   * Returns product attribute.
   *
   * @param {object} product
   *  GE product object.
   * @param {string} key
   *  Product attribute key.
   *
   * @return {string}
   *   Returns attribute value.
   */
  Drupal.getProductMetadata = function (product, key) {
    for (let i = 0; i < product.MetaData.length; i++) {
      if (product.MetaData[i].AttributeKey === key) {
        return product.MetaData[i].AttributeValue;
      }
    }
  };

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
      switch (step) {
        case 2:
          if (Drupal.hasValue(geData.details.PaymentMethods) && geData.details.PaymentMethods.length > 0) {
            // Populate drupal settings with details from GE data.
            drupalSettings.payment_methods['global-e'] = geData.details.PaymentMethods[0].PaymentMethodTypeName;
          }
          Drupal.alshayaSeoSpc.checkoutEvent(Drupal.mapGlobaleCheckoutData(geData), step);
          break;

        case 3:
          var cartData = Drupal.mapGlobaleStepData(geData, 3);
          dataLayer.push(cartData);
          break;

        case 4:
          // Push step 4 checkout data to data layer.
          var cartData = Drupal.mapGlobaleStepData(geData, 4);
          dataLayer.push(cartData);

          var purchaseSuccessData = Drupal.mapGlobalePurchaseSuccessData(geData);
          if (purchaseSuccessData) {
            dataLayer.push(purchaseSuccessData);
          }
          break;
      }
    }
    catch (error) {
      Drupal.logJavascriptError("Alshaya XB Checkout", error, GTM_CONSTANTS.CHECKOUT_ERRORS);
    }
  };

  /**
   * Helper function to map the Global-e checkout data to step 4 gtm data.
   *
   * @param {object} geData
   *   Global-e checkout data.
   * @param {string} step
   *   Checkout step.
   *
   * @return {object}
   *   The cart data object.
   */
  Drupal.mapGlobaleStepData = function (geData, step) {

    // Process product data.
    let productSku = [];
    let productStyleCode = [];
    let cartItemsCount = 0;
    let productGtm = [];
    let cartItemsFlocktory = [];
    if (geData.details.ProductInformation) {
      Object.entries(geData.details.ProductInformation).forEach(function (productItem) {
        var product = productItem[1];
        var productGtmData = {
          "quantity" : product.Quantity,
          "name" : product.ProductName,
          "id" : product.ProductGroupCode,
          "price" : product.ProductPrices.MerchantTransaction.DiscountedPrice,
          "brand" : product.Brand,
          "category" : Drupal.getProductMetadata(product, 'category'),
          "variant" : product.SKU,
          "dimension2" : Drupal.getProductMetadata(product, 'dimension2'),
          "dimension3" : (product.ProductPrices.MerchantTransaction.TotalPrice !== product.ProductPrices.MerchantTransaction.DiscountedPrice) ? 'Discounted Product' : 'Regular Product',
          "dimension4" : Drupal.getProductMetadata(product, 'dimension4'),
          "productOldPrice" : product.ProductPrices.MerchantTransaction.ListPrice,
        };
        productGtm.push(productGtmData);
        productSku.push(product.SKU);
        productStyleCode.push(product.ProductGroupCode);
        cartItemsCount = parseInt(product.Quantity) + cartItemsCount;
        let cartItem = {
          "id" : product.ProductGroupCode,
          "price" : product.ProductPrices.MerchantTransaction.DiscountedPrice,
          "count" : product.Quantity,
          "title" : product.ProductName,
          "image" : '', // @todo get product media from global-e.
        };
        cartItemsFlocktory.push(cartItem);
      });
    }

    var cartData =  {
      "language": drupalSettings.gtm.language,
      "country": drupalSettings.gtm.country,
      "currency": drupalSettings.gtm.currency,
      "pageType": drupalSettings.gtm.pageType,
      "event": 'checkout',
      "ecommerce": {
        "currencyCode" : drupalSettings.gtm.currency,
        "checkout": {
          "actionField": {
            "step": step,
          },
          "products": productGtm
        },
      },
      // Click and collect is not available on XB sites.
      "deliveryOption" : 'Home Delivery',
      "deliveryType" : geData.details.ShippingMethodType,
      "deliveryCity" : geData.details.CustomerDetails.ShippingAddress.ShippingCity,
      "deliveryArea" : geData.details.CustomerDetails.ShippingAddress.ShippingCityRegion,
      "privilegeCustomer": drupalSettings.userDetails.privilegeCustomer ? 'Privilege Customer' : 'Regular Customer',
      "privilegesCardNumber": null, // @todo We need to ask Global-e to get this information.
      "productSKU" : productSku,
      "productStyleCode" : productStyleCode,
      "cartItemsCount" : cartItemsCount,
      "cartItemsFlocktory" : cartItemsFlocktory,
    };

    if (step == 3) {
      cartData.xbDeliveryInfo = geData.xbDeliveryInfo;
      cartData.cartTotalValue = geData.details.OrderPrices.CustomerTransactionInMerchantCurrency.CustomerTotalProductsPriceInMerchantCurrency;
    }
    else if (step == 4) {
      cartData.paymentOption = geData.details.PaymentMethods[0].PaymentMethodTypeName;
      cartData.cartTotalValue = geData.details.OrderPaymentMethods[0].PaidAmountInMerchantCurrency;
    }
    return cartData;
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
        var product = productItem[1];
        var productGtmData = {
          "item_id": product.CartItemId,
          "sku": product.SKU,
          "qty": product.Quantity,
          "name": product.ProductName,
          "price": product.ProductPrices.MerchantTransaction.DiscountedPrice,
          "finalPrice": product.ProductPrices.MerchantTransaction.DiscountedPrice,
        };
        cartItemsCount = parseInt(product.Quantity) + cartItemsCount;
        productGtm.push(productGtmData);
      });
    }

    // Loop the Discounts array and calculate the discount amount.
    let discountAmount = 0;
    if (Drupal.hasValue(geData.details.Discounts)) {
      geData.details.Discounts.forEach(function (item) {
        if (Drupal.hasValue(item.DiscountTypeId) && item.DiscountPrices.CustomerTransactionInMerchantCurrency.CustomerPriceInMerchantCurrency && item.DiscountTypeId == 1) {
          discountAmount += item.DiscountPrices.CustomerTransactionInMerchantCurrency.CustomerPriceInMerchantCurrency;
        }
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
        "subtotal_incl_tax": geData.details.OrderPrices.CustomerTransactionInMerchantCurrency.CustomerTotalPriceInMerchantCurrency,
        "base_grand_total": geData.details.OrderPrices.CustomerTransactionInMerchantCurrency.CustomerTotalDiscountedProductsPriceInMerchantCurrency,
        "base_grand_total_without_surcharge": geData.details.OrderPrices.CustomerTransactionInMerchantCurrency.CustomerTotalDiscountedProductsPriceInMerchantCurrency,
        "discount_amount": discountAmount,
        "surcharge": geData.details.OrderPrices.CustomerTransactionInMerchantCurrency.Fees.CustomerRemoteAreaSurchargeFeeInMerchantCurrency,
        "shipping_incl_tax": geData.details.OrderPrices.CustomerTransactionInMerchantCurrency.CustomerShippingPriceInMerchantCurrency + geData.details.OrderPrices.CustomerTransactionInMerchantCurrency.CustomerVATInMerchantCurrency
      },
      "items": productGtm,
    };
  };

  /**
   * Helper function to map the Global-e purchase success event data.
   *
   * @param {object} geData
   *   Global-e checkout data.
   *
   * @return {object}
   *   The purchase success data object.
   */
  Drupal.mapGlobalePurchaseSuccessData = function (geData) {
    let productGtm = [];
    let cartItemsCount = 0;
    let productSku = [];
    let productStyleCode = [];
    let discountAmount = 0;
    let firstTimeTransaction = null;
    if (geData.details.ProductInformation) {
      Object.entries(geData.details.ProductInformation).forEach(function (productItem) {
        var product = productItem[1];
        var productGtmData = {
          "name": product.ProductName,
          "id": product.CartItemId,
          "price": product.ProductPrices.CustomerTransactionInMerchantCurrency.CustomerDiscountedPriceInMerchantCurrency,
          "brand": product.Brand,
          "category": Drupal.getProductMetadata(product, 'category'),
          "variant": product.ProductGroupCode,
          "dimension2" : Drupal.getProductMetadata(product, 'dimension2'),
          "dimension4" : Drupal.getProductMetadata(product, 'dimension4'),
          "dimension3": (product.ProductPrices.MerchantTransaction.TotalPrice !== product.ProductPrices.MerchantTransaction.DiscountedPrice) ? 'Discounted Product' : 'Regular Product',
          "quantity": product.Quantity
        };
        productGtm.push(productGtmData);
        productSku.push(product.SKU);
        productStyleCode.push(product.ProductGroupCode);
        cartItemsCount = parseInt(product.Quantity) + cartItemsCount;
        discountAmount = product.ProductPrices.MerchantTransaction.DiscountedPrice;
        firstTimeTransaction = Drupal.getProductMetadata(product, 'firstTimeTransaction');
      });
    }

    return {
      "language": drupalSettings.gtm.language,
      "country": drupalSettings.gtm.country,
      "currency": drupalSettings.gtm.currency,
      "deliveryOption": geData.details.ShippingMethodName,
      "deliveryType": geData.details.ShippingMethodType,
      "paymentOption": geData.details.OrderPaymentMethods[0].PaymentMethodTypeName,
      "egiftRedeemType": geData.details.GiftCards,
      "isAdvantageCard": null, // @todo We need to ask Global-e to get this information.
      "redeemEgiftCardValue": null, // @todo We need to ask Global-e to get this information.
      "discountAmount": discountAmount,
      "transactionId": geData.OrderId,
      "globaleId": geData.OrderId,
      "firstTimeTransaction": firstTimeTransaction || true,
      "privilegesCardNumber": null, // @todo We need to ask Global-e to get this information.
      "loyaltyType": null, // @todo We need to ask Global-e to get this information.
      "rewardType": null, // @todo We need to ask Global-e to get this information.
      "userId": drupalSettings.userDetails.userID,
      "userEmailID": geData.details.CustomerDetails.BillingAddress.BillingEmail,
      "userName": geData.details.CustomerDetails.BillingAddress.BillingFirstName + " " + geData.details.CustomerDetails.BillingAddress.BillingLastName,
      "userPhone": geData.details.CustomerDetails.BillingAddress.BillingPhoneNumber,
      "userType": drupalSettings.userDetails.userType,
      "customerType": drupalSettings.userDetails.customerType,
      "platformType": drupalSettings.userDetails.platformType,
      "paymentMethodsUsed": [
        geData.details.OrderPaymentMethods[0].PaymentMethodTypeName,
      ],
      "deliveryInfo": {
        "country_code": geData.details.CustomerDetails.ShippingAddress.country_code,
        "given_name": geData.details.CustomerDetails.ShippingAddress.ShippingFirstName,
        "family_name": geData.details.CustomerDetails.ShippingAddress.ShippingLastName,
        "mobile_number": {
          "value": geData.details.CustomerDetails.ShippingAddress.ShippingPhoneNumber,
        },
        "address_line1": geData.details.CustomerDetails.ShippingAddress.ShippingAddress1,
        "address_line2": geData.details.CustomerDetails.ShippingAddress.ShippingAddress2,
        "locality": geData.details.CustomerDetails.ShippingAddress.ShippingCityRegion,
        "dependent_locality": geData.details.CustomerDetails.ShippingAddress.ShippingZipCode,
        "administrative_area": null, // @todo We need to ask Global-e top get this information.
        "area_parent": null, // @todo We need to ask Global-e top get this information.
        "area_parent_display": null, // @todo We need to ask Global-e top get this information.
        "administrative_area_display": geData.details.CustomerDetails.ShippingAddress.ShippingCity,
      },
      "delivery_city": geData.details.CustomerDetails.ShippingAddress.ShippingCity,
      "privilegeOrder": drupalSettings.userDetails.privilegeCustomer ? 'order with privilege club' : 'order without privilege club',
      "event": "purchaseSuccess",
      "with_delivery_schedule": null, // @todo We need to ask Global-e top get this information.
      "ecommerce": {
        "currencyCode": geData.details.MerchantCurrencyCode,
        "purchase": {
          "actionField": {
            "id": geData.OrderId,
            "affiliation": "Online Store",
            "revenue": geData.details.OrderPrices.CustomerTransactionInMerchantCurrency.CustomerTotalPriceInMerchantCurrency,
            "tax": geData.details.OrderPrices.CustomerTransactionInMerchantCurrency.CustomerDutiesAndTaxesInMerchantCurrency,
            "shipping": geData.details.OrderPrices.CustomerTransactionInMerchantCurrency.CustomerShippingPriceInMerchantCurrency,
            "coupon": Drupal.hasValue(geData.details.Discounts) ? geData.details.Discounts[0].coupon : '',
            "action": "purchase"
          },
          "products": [
            productGtm
          ]
        }
      },
      "isEgiftCard": geData.details.OrderPaymentMethods[0].IsGiftCard ? "yes" : "no",
      "pageType": drupalSettings.gtm.pageType,
      "productSKU": productSku,
      "productStyleCode": productStyleCode,
      "cartTotalValue": geData.details.OrderPaymentMethods[0].PaidAmountInMerchantCurrency,
      "cartItemsCount": cartItemsCount,
      "deliveryArea": geData.details.CustomerDetails.ShippingAddress.ShippingCityRegion,
      "deliveryCity": geData.details.CustomerDetails.ShippingAddress.ShippingCity,
    };
  };
})(Drupal, dataLayer);
