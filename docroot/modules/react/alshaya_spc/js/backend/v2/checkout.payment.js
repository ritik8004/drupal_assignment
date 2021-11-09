import _isEmpty from 'lodash/isEmpty';
import { callMagentoApi, getCart } from './common';
import { getApiEndpoint } from './utility';
import logger from '../../utilities/logger';
import StaticStorage from './staticStorage';
import { addPaymentMethodInCart } from '../../utilities/update_cart';
import cartActions from '../../utilities/cart_actions';
import { getUserFriendlyErrorMessage } from './error';

window.commerceBackend = window.commerceBackend || {};

/**
 * Gets payment methods.
 *
 * @returns {Promise<object|null>}.
 *   The method list if available.
 */
const getPaymentMethods = async () => {
  const cart = await getCart();

  if (_isEmpty(cart) || _isEmpty(cart.data) || !_isEmpty(cart.data.error)) {
    logger.error('Cart not available or there is an error, not loading payment methods.');
    return null;
  }

  if (_isEmpty(cart.data.shipping) || _isEmpty(cart.data.shipping.method)) {
    logger.notice('Shipping method not available, not loading payment methods. CartID: @cartId.', {
      '@cartId': cart.data.id,
    });

    return null;
  }

  // Change the payment methods based on shipping method.
  const staticCacheKey = `payment_methods_${cart.data.shipping.type}`;
  const cached = StaticStorage.get(staticCacheKey);
  if (!_isEmpty(cached)) {
    return cached;
  }

  const cartId = window.commerceBackend.getCartId();

  // Get payment methods from MDC.
  const response = await callMagentoApi(getApiEndpoint('getPaymentMethods', { cartId }), 'GET', {});

  let paymentMethods = {};

  if (!_isEmpty(response.data)) {
    paymentMethods = response.data;
    StaticStorage.set(staticCacheKey, paymentMethods);
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
  const cached = StaticStorage.get('payment_method');
  if (!_isEmpty(cached)) {
    return cached;
  }

  const params = {
    cartId: window.commerceBackend.getCartId(),
  };
  const response = await callMagentoApi(getApiEndpoint('selectedPaymentMethod', params), 'GET', {});
  if (!_isEmpty(response) && !_isEmpty(response.data) && !_isEmpty(response.data.method)) {
    StaticStorage.set('payment_method', response.data.method);
    return response.data.method;
  }

  // Log if there is an error.
  if (!_isEmpty(response.data.error)) {
    logger.error('Error while getting payment set on cart. Response: @response', {
      '@response': JSON.stringify(response.data),
    });

    response.data.error_message = getUserFriendlyErrorMessage(response);
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
    if (!_isEmpty(response.response_message)
      && !_isEmpty(response.response_message.status)
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
      '@message': !_isEmpty(response.error) ? response.error.message : response,
      '@errorCode': !_isEmpty(response.error) ? response.error.error_code : '',
    });

    return response;
  });
};

export {
  getPaymentMethods,
  getPaymentMethodSetOnCart,
};
