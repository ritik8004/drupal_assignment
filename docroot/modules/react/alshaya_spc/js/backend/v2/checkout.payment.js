import _isEmpty from 'lodash/isEmpty';
import { callMagentoApi, getCart } from './common';
import { getApiEndpoint, logger } from './utility';
import StaticStorage from './staticStorage';

/**
 * Gets payment methods.
 *
 * @returns {Promise<object|null>}.
 *   The method list if available.
 */
const getPaymentMethods = async () => {
  const cart = await getCart();

  // Change the payment methods based on shipping method and cart total.
  const staticCacheKey = `payment_methods_${cart.data.shipping.type}_${cart.data.totals.base_grand_total}`;
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
  }

  return null;
};

export {
  getPaymentMethods,
  getPaymentMethodSetOnCart,
};
