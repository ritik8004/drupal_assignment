import { getCart } from './common';
import {
  getApiEndpoint,
  isRequestFromSocialAuthPopup,
} from './utility';
import logger from '../../../../js/utilities/logger';
import { addPaymentMethodInCart } from '../../utilities/update_cart';
import cartActions from '../../utilities/cart_actions';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import { callMagentoApi } from '../../../../js/utilities/requestHelper';
import { cartContainsOnlyVirtualProduct } from '../../utilities/egift_util';

window.commerceBackend = window.commerceBackend || {};

/**
 * Gets payment methods.
 *
 * @returns {Promise<object|null>}.
 *   The method list if available.
 */
const getPaymentMethods = async () => {
  // If request is from SocialAuth Popup, restrict further processing.
  // we don't want magento API calls happen on popup, As this is causing issues
  // in processing parent pages.
  if (isRequestFromSocialAuthPopup()) {
    return null;
  }

  const cart = await getCart();

  if (!hasValue(cart) || !hasValue(cart.data) || hasValue(cart.data.error)) {
    logger.error('Cart not available or there is an error, not loading payment methods.');
    return null;
  }

  // This condition should not be validated when egift is enabled.
  if (!cartContainsOnlyVirtualProduct(cart.data.cart)
    && (!hasValue(cart.data.shipping) || !hasValue(cart.data.shipping.method))) {
    logger.notice('Shipping method not available, not loading payment methods. CartID: @cartId.', {
      '@cartId': cart.data.id,
    });

    return null;
  }

  // Change the payment methods based on shipping method.
  const staticCacheKey = `payment_methods_${cart.data.shipping.type}_${cart.data.shipping.method}`;
  const cached = Drupal.alshayaSpc.staticStorage.get(staticCacheKey);
  if (hasValue(cached)) {
    return cached;
  }

  const cartId = window.commerceBackend.getCartId();

  // Get payment methods from MDC.
  const response = await callMagentoApi(getApiEndpoint('getPaymentMethods', { cartId }), 'GET', {});

  let paymentMethods = {};

  if (hasValue(response.data)) {
    paymentMethods = response.data;
    Drupal.alshayaSpc.staticStorage.set(staticCacheKey, paymentMethods);
  }

  return paymentMethods;
};

/**
 * Get the payment method set on cart.
 *
 * @return {Promise<string|null>}.
 *   Payment method set on cart.
 */
const getPaymentMethodSetOnCart = async () => {
  // If request is from SocialAuth Popup, restrict further processing.
  // we don't want magento API calls happen on popup, As this is causing issues
  // in processing parent pages.
  if (isRequestFromSocialAuthPopup()) {
    return null;
  }

  const cached = Drupal.alshayaSpc.staticStorage.get('payment_method');
  if (hasValue(cached)) {
    return cached;
  }

  const params = {
    cartId: window.commerceBackend.getCartId(),
  };
  const response = await callMagentoApi(getApiEndpoint('selectedPaymentMethod', params), 'GET', {});
  if (hasValue(response) && hasValue(response.data) && hasValue(response.data.method)) {
    Drupal.alshayaSpc.staticStorage.set('payment_method', response.data.method);
    return response.data.method;
  }

  // Log if there is an error.
  if (hasValue(response.data.error)) {
    logger.error('Error while getting payment set on cart. Response: @response', {
      '@response': JSON.stringify(response.data),
    });
  }

  return null;
};

/**
 * Checkout.com Apple pay update payment method.
 *
 * @param {object} data
 *   The data object to send in the API call.
 *
 * @returns {Promise}
 *   A promise object.
 */
window.commerceBackend.saveApplePayPayment = (data) => {
  logger.debug('Inside window.commerceBackend.saveApplePayPayment.');

  const paymentData = {
    payment: {
      method: 'checkout_com_applepay',
      additional_data: {
        data: data.paymentData.data,
        ephemeralPublicKey: data.paymentData.header.ephemeralPublicKey,
        publicKeyHash: data.paymentData.header.publicKeyHash,
        transactionId: data.paymentData.header.transactionId,
        signature: data.paymentData.signature,
        version: data.paymentData.version,
        paymentMethodDisplayName: data.paymentMethod.displayName,
        paymentMethodNetwork: data.paymentMethod.network,
        paymentMethodType: data.paymentMethod.type,
      },
    },
  };

  return addPaymentMethodInCart(cartActions.cartPaymentUpdate, paymentData).then((response) => {
    if (hasValue(response.response_message)
      && hasValue(response.response_message.status)
      && response.response_message.status === 'success') {
      return {
        data: {
          success: true,
        },
      };
    }

    return response;
  }).catch((response) => {
    logger.error('Error while finalizing payment. Error message: @message, Code: @errorCode.', {
      '@message': hasValue(response.error) ? response.error.message : response,
      '@errorCode': hasValue(response.error) ? response.error.error_code : '',
    });

    return response;
  });
};

export {
  getPaymentMethods,
  getPaymentMethodSetOnCart,
};
