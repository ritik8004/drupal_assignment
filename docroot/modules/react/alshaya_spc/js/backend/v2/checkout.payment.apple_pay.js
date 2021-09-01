import _isEmpty from 'lodash/isEmpty';
import { addPaymentMethodInCart } from '../../utilities/update_cart';
import cartActions from '../../utilities/cart_actions';
import { logger } from './utility';

window.commerceBackend = window.commerceBackend || {};

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
    logger.error('Error while finalizing payment. Error message: @message, Code: @code.', {
      '@message': !_isEmpty(response.error) ? response.error.message : response,
      '@code': !_isEmpty(response.error) ? response.error.error_code : '',
    });

    return response;
  });
};
