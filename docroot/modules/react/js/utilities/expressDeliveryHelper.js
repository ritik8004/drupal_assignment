import { hasValue } from './conditionsUtility';
import { callMagentoApi } from './requestHelper';
import logger from './logger';

/**
 * Helper function to check if Express delivery is enabled.
 */
const isExpressDeliveryEnabled = () => {
  if (typeof drupalSettings.expressDelivery !== 'undefined'
    && typeof drupalSettings.expressDelivery.enabled !== 'undefined') {
    return drupalSettings.expressDelivery.enabled;
  }

  return false;
};

/**
 * Helper function to check if Express delivery applicable for sku.
 */
const checkProductExpressDeliveryStatus = (sku) => {
  if (typeof drupalSettings.productInfo !== 'undefined'
    && typeof drupalSettings.expressDelivery !== 'undefined'
    && typeof drupalSettings.productInfo[sku].expressDelivery !== 'undefined') {
    return drupalSettings.productInfo[sku].expressDelivery;
  }

  return false;
};

/**
 * Helper function to check if ED/SDD is present
 * in the shipping methods array.
 */
const checkShippingMethodsStatus = (shippingMethods) => {
  let count = 0;
  if (typeof shippingMethods !== 'undefined') {
    shippingMethods.forEach((shippingMethod) => {
      if (shippingMethod.available !== 'undefined' && shippingMethod.available) {
        if ((shippingMethod.carrier_code === 'SAMEDAY') || (shippingMethod.carrier_code === 'EXPRESS')) {
          count += 1;
        }
      }
    });
    if (count > 0) {
      return true;
    }
  }

  return false;
};

/**
 * Helper function to check if ED/SDD is disabled for
 * all the products in cart.
 */
const checkAreaAvailabilityStatusOnCart = (cartShippingMethods) => {
  let show = false;
  if (typeof cartShippingMethods !== 'undefined' && !hasValue(cartShippingMethods.error)) {
    cartShippingMethods.forEach((cartShippingMethodsList) => {
      const shippingMethods = cartShippingMethodsList.applicable_shipping_methods;
      const status = checkShippingMethodsStatus(shippingMethods);
      if (status === true) {
        show = true;
      }
    });
    return show;
  }
  return true;
};

/**
 * Helper function to check if Express delivery is enabled.
 */
const checkSameDayDeliveryStatus = () => {
  if (typeof drupalSettings.expressDelivery !== 'undefined'
    && typeof drupalSettings.expressDelivery.same_day_delivery !== 'undefined') {
    return drupalSettings.expressDelivery.same_day_delivery;
  }

  return false;
};

/**
 * Helper function to check if Express delivery is enabled.
 */
const checkExpressDeliveryStatus = () => {
  if (typeof drupalSettings.expressDelivery !== 'undefined'
    && typeof drupalSettings.expressDelivery.express_delivery !== 'undefined') {
    return drupalSettings.expressDelivery.express_delivery;
  }

  return false;
};

/**
 * Gets the express delivery configuration from magento for listing pages.
 */
async function getExpressDeliveryStatus() {
  // Get express-delivery settings from MDC for labels display.
  // Here we don't pass any sku, we only pass get_config_details as true
  // in order to MDC configuration for listing page to control the display of
  // Express delivery label on teaser.
  const url = '/V1/deliverymatrix/get-applicable-shipping-methods';
  const params = {
    productAndAddressInformation: {
      cart_id: null,
      product_sku: null,
      address: {
        custom_attributes: [],
      },
      get_config_details: true,
    },
  };

  let labelStatus = true;

  try {
    const response = await callMagentoApi(url, 'POST', params);
    if (!hasValue(response.data) || hasValue(response.data.error)) {
      logger.error('Error occurred while fetching governates, Response: @response.', {
        '@response': JSON.stringify(response.data),
      });
      return null;
    }

    response.data.forEach((label) => {
      labelStatus = (label.carrier_code.toString() === 'SAMEDAY' || label.carrier_code.toString() === 'EXPRESS') && label.status;
    });

    // Dispatch event for teaser component as they will rendered before the
    // api response.
    const event = new CustomEvent('expressDeliveryLabelsDisplay', {
      bubbles: true,
      detail: window.expressDeliveryLabel,
    });
    document.dispatchEvent(event);
  } catch (error) {
    logger.error('Error occurred while fetching the delivery status config for listing. Message: @message.', {
      '@message': error.message,
    });
  }

  return labelStatus;
}

export {
  isExpressDeliveryEnabled,
  checkProductExpressDeliveryStatus,
  checkShippingMethodsStatus,
  checkAreaAvailabilityStatusOnCart,
  checkSameDayDeliveryStatus,
  checkExpressDeliveryStatus,
  getExpressDeliveryStatus,
};
