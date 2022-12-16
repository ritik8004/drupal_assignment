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
  let isError = false;
  if (typeof shippingMethods !== 'undefined') {
    shippingMethods.forEach((shippingMethod) => {
      if (typeof shippingMethod === 'string') {
        logger.error('Error occurred while fetching the shipping method. Shipping method: @response.', {
          '@response': shippingMethod,
        });
        isError = true;
      }
      if (shippingMethod.available !== 'undefined' && shippingMethod.available) {
        if ((shippingMethod.carrier_code === 'SAMEDAY') || (shippingMethod.carrier_code === 'EXPRESS')) {
          count += 1;
        }
      }
    });
    if (count > 0 && !isError) {
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
  // Validate if SDD/ED is enabled or not.
  if (!isExpressDeliveryEnabled()) {
    return {
      sameDayDelivery: false,
      expressDelivery: false,
    };
  }

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

  const showExpressDeliveryLabel = {
    sameDayDelivery: true,
    expressDelivery: true,
  };

  try {
    const response = await callMagentoApi(url, 'POST', params);
    if (!hasValue(response.data) || hasValue(response.data.error)) {
      logger.warning('Error occurred while fetching the express-delivery config for listing., Response: @response.', {
        '@response': JSON.stringify(response.data),
      });
      // Dispatch event for delivery label component with default true as
      // error response from magento.
      const event = new CustomEvent('expressDeliveryLabelsDisplay', {
        detail: showExpressDeliveryLabel,
      });
      document.dispatchEvent(event);
    }
    if (Array.isArray(response.data)) {
      response.data.forEach((label) => {
        if (label.carrier_code === 'SAMEDAY') {
          showExpressDeliveryLabel.sameDayDelivery = label.status;
        } else if (label.carrier_code === 'EXPRESS') {
          showExpressDeliveryLabel.expressDelivery = label.status;
        }
      });
    }

    // Dispatch event for delivery label component on teaser with API response
    // in event details.
    const event = new CustomEvent('expressDeliveryLabelsDisplay', {
      detail: showExpressDeliveryLabel,
    });
    document.dispatchEvent(event);
  } catch (error) {
    logger.warning('Error occurred while fetching the express-delivery config for listing. Message: @message.', {
      '@message': error.message,
    });
    // Dispatch event for delivery label component with default true as
    // error response from magento.
    const event = new CustomEvent('expressDeliveryLabelsDisplay', {
      detail: showExpressDeliveryLabel,
    });
    document.dispatchEvent(event);
  }

  return showExpressDeliveryLabel;
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
