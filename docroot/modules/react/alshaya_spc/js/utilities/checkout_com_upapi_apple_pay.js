import Axios from 'axios';
import {
  getUpapiApplePayConfig,
  placeOrder,
  removeFullScreenLoader,
} from './checkout_util';
import dispatchCustomEvent from './events';
import getStringMessage from './strings';
import { addPaymentMethodInCart } from './update_cart';
import cartActions from './cart_actions';
import logger from '../../../js/utilities/logger';

let applePaySessionObject;

const CheckoutComUpapiApplePay = {
  isAvailable: () => {
    if (!(window.ApplePaySession)) {
      return false;
    }

    const isMobile = ('ontouchstart' in document.documentElement && navigator.userAgent.match(/Mobi/));
    return drupalSettings.checkoutComUpapiApplePay.allowedIn === 'all' || isMobile;
  },

  isPossible: () => {
    document.getElementById('is-possible-placeholder').classList.add('error');

    const identifier = drupalSettings.checkoutComUpapiApplePay.apple_pay_merchant_id;
    window.ApplePaySession.canMakePaymentsWithActiveCard(identifier).then((canMakePayments) => {
      removeFullScreenLoader();
      if (canMakePayments) {
        document.getElementById('is-possible-placeholder').classList.remove('error');
      } else {
        Drupal.logJavascriptError(
          'apple-pay-checking-isPossible',
          'user cannot make payments',
          GTM_CONSTANTS.PAYMENT_ERRORS,
        );
      }

      dispatchCustomEvent('refreshCompletePurchaseSection', {});
    }).catch((error) => {
      removeFullScreenLoader();
      document.getElementById('is-possible-placeholder').classList.add('error');
      dispatchCustomEvent('refreshCompletePurchaseSection', {});
      Drupal.logJavascriptError('apple-pay-checking-isPossible', error.message, GTM_CONSTANTS.PAYMENT_ERRORS);
    });
  },

  getApplePaySupportedVersion: () => {
    // When this code is written max version supported is 6. We would need
    // to test new versions whenever required to upgrade so not making it a
    // config. Same for minimum version.
    for (let i = 6; i > 1; i--) {
      if (window.ApplePaySession.supportsVersion(i)) {
        return i;
      }
    }

    return 1;
  },

  onValidateMerchant: (event) => {
    logger.debug('Inside onValidateMerchant for Apple Pay UPAPI.');

    const controllerUrl = Drupal.url('checkoutcom/upapi/applepay/validate');
    const validationUrl = `${controllerUrl}?u=${event.validationURL}`;
    Axios.get(validationUrl).then((merchantSession) => {
      logger.debug('Merchant validation successful for Apple Pay UPAPI.');
      applePaySessionObject.completeMerchantValidation(merchantSession.data);
    }).catch((error) => {
      dispatchCustomEvent('spcCheckoutMessageUpdate', {
        type: 'error',
        message: getStringMessage('payment_error'),
      });
      removeFullScreenLoader();
      Drupal.logJavascriptError('apple-pay-merchant-validation', error.message, GTM_CONSTANTS.PAYMENT_ERRORS);
    });
  },

  onPaymentAuthorized: (event) => {
    logger.debug('Inside onPaymentAuthorized for Apple Pay UPAPI.');

    const upApiApplePayConfig = getUpapiApplePayConfig();
    const url = upApiApplePayConfig.api_url;
    const { token } = event.payment;
    const params = {
      type: 'applepay',
      token_data: {
        version: token.paymentData.version,
        data: token.paymentData.data,
        signature: token.paymentData.signature,
        header: {
          ephemeralPublicKey: token.paymentData.header.ephemeralPublicKey,
          publicKeyHash: token.paymentData.header.publicKeyHash,
          transactionId: token.transactionIdentifier,
        },
      },
    };
    Axios.post(url, params, {
      headers: {
        Authorization: upApiApplePayConfig.public_key,
      },
    }).then((response) => {
      if (response.data.type !== undefined && response.data.type === 'applepay') {
        const paymentData = {
          payment: {
            method: 'checkout_com_upapi_applepay',
            additional_data: {
              token: response.data.token,
              bin: response.data.bin,
              type: response.data.type,
              expires_on: response.data.expires_on,
              expiry_month: response.data.expiry_month,
              expiry_year: response.data.expiry_year,
              last4: response.data.last4,
            },
          },
        };

        // Update payment method with token data.
        addPaymentMethodInCart(cartActions.cartPaymentFinalise, paymentData).then((result) => {
          if (!result) {
            // Something wrong, throw error.
            throw (new Error(response.data.error_message));
          }

          if (result.error === undefined) {
            // Update apple pay payment sheet.
            applePaySessionObject.completePayment(window.ApplePaySession.STATUS_SUCCESS);

            // Place order.
            placeOrder('checkout_com_upapi_apple_pay');
          }
        }).catch((error) => {
          // Update apple pay popup.
          applePaySessionObject.completePayment(window.ApplePaySession.STATUS_FAILURE);
          dispatchCustomEvent('spcCheckoutMessageUpdate', {
            type: 'error',
            message: getStringMessage('payment_error'),
          });
          Drupal.logJavascriptError('add payment method in cart', error, GTM_CONSTANTS.GENUINE_PAYMENT_ERRORS);
        });

        return;
      }

      // Something wrong, throw error.
      throw (new Error(response.data.error_message));
    }).catch((error) => {
      // Update apple pay popup.
      applePaySessionObject.completePayment(window.ApplePaySession.STATUS_FAILURE);
      dispatchCustomEvent('spcCheckoutMessageUpdate', {
        type: 'error',
        message: getStringMessage('payment_error'),
      });
      removeFullScreenLoader();
      Drupal.logJavascriptError('apple-pay-checkoutcom-get-token', error.message, GTM_CONSTANTS.PAYMENT_ERRORS);
    });
  },

  onCancel: () => {
    removeFullScreenLoader();
    Drupal.logJavascriptError('apple-pay', 'user cancelled or error occurred', GTM_CONSTANTS.PAYMENT_ERRORS);
  },

  startPayment: (total) => {
    const upApiApplePayConfig = getUpapiApplePayConfig();
    // Some features are not supported in some versions, this is browser
    // specific so done in JS.
    // Mada is supported only from version 5 onwards.
    let networks = upApiApplePayConfig.apple_pay_supported_networks.split(',');
    if (CheckoutComUpapiApplePay.getApplePaySupportedVersion() < 5) {
      networks = networks.filter((element) => element !== 'mada');
    }

    // Prepare the parameters.
    const paymentRequest = {
      merchantIdentifier: upApiApplePayConfig.apple_pay_merchant_id,
      currencyCode: upApiApplePayConfig.currencyCode,
      countryCode: upApiApplePayConfig.countryId,
      total: {
        label: upApiApplePayConfig.storeName,
        amount: total,
      },
      supportedNetworks: networks,
      merchantCapabilities: upApiApplePayConfig.apple_pay_merchant_capabilities.split(','),
    };

    // Start the payment session.
    try {
      applePaySessionObject = new window.ApplePaySession(1, paymentRequest);
      applePaySessionObject.onvalidatemerchant = CheckoutComUpapiApplePay.onValidateMerchant;
      applePaySessionObject.onpaymentauthorized = CheckoutComUpapiApplePay.onPaymentAuthorized;
      applePaySessionObject.oncancel = CheckoutComUpapiApplePay.onCancel;
      applePaySessionObject.begin();
    } catch (e) {
      Drupal.logJavascriptError('Apple pay session error', e.message, GTM_CONSTANTS.PAYMENT_ERRORS);
      dispatchCustomEvent('spcCheckoutMessageUpdate', {
        type: 'error',
        message: getStringMessage('payment_error'),
      });
    }
  },
};

export default CheckoutComUpapiApplePay;
