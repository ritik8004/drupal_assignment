/**
 * @file
 * Push SEO data to datalayer.
 */

(function (Drupal, dataLayer) {
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
          var cartData = Drupal.mapGlobaleCheckoutData(geData);
          // Set XB delivery info.
          cartData.xbDeliveryInfo = geData.xbDeliveryInfo;
          Drupal.alshayaSeoSpc.checkoutEvent(cartData, step);
          break;

        case 4:
          const purchaseSuccessData = Drupal.mapGlobalePurchaseSuccessData(geData);
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
    if (geData.details.ProductInformation) {
      Object.entries(geData.details.ProductInformation).forEach(function (productItem) {
        const product = productItem[1];
        const productGtmData = {
          "name": product.ProductName,
          "id": product.CartItemId,
          "price": product.ProductPrices.CustomerTransactionInMerchantCurrency.CustomerDiscountedPriceInMerchantCurrency,
          "brand": product.Brand,
          "category": null, // @todo We need to ask Global-e to get this information.
          "variant": product.ProductGroupCode,
          "dimension2": null, // @todo This is not directly coming from global-e. We need to find a way to get this value.
          "dimension4": null, // @todo This is not directly coming from global-e. We need to find a way to get this value.
          "dimension3": (product.ProductPrices.MerchantTransaction.TotalPrice !== product.ProductPrices.MerchantTransaction.DiscountedPrice) ? 'Discounted Product' : 'Regular Product',
          "quantity": product.Quantity
        };
        productGtm.push(productGtmData);
        productSku.push(product.SKU);
        productStyleCode.push(product.ProductGroupCode);
        cartItemsCount = parseInt(product.Quantity) + cartItemsCount;
        discountAmount = product.ProductPrices.MerchantTransaction.DiscountedPrice;
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
      "firstTimeTransaction": null, // @todo We need to expose this via drupalSettings.
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
