import Axios from 'axios';
import { placeOrder, removeFullScreenLoader } from './checkout_util';
import dispatchCustomEvent from './events';
import getStringMessage from './strings';
import i18nMiddleWareUrl from './i18n_url';

let applePaySessionObject;

const ApplePay = {
  isAvailable: () => {
    if (!(window.ApplePaySession)) {
      return false;
    }

    const isMobile = ('ontouchstart' in document.documentElement && navigator.userAgent.match(/Mobi/));
    return drupalSettings.checkoutComApplePay.allowedIn === 'all' || isMobile;
  },

  isPossible: () => {
    document.getElementById('is-possible-placeholder').classList.add('error');

    const identifier = drupalSettings.checkoutComApplePay.merchantIdentifier;
    window.ApplePaySession.canMakePaymentsWithActiveCard(identifier).then((canMakePayments) => {
      removeFullScreenLoader();
      if (canMakePayments) {
        document.getElementById('is-possible-placeholder').classList.remove('error');
      } else {
        Drupal.logJavascriptError(
          'apple-pay-checking-isPossible',
          'user cannot make payments',
          'payment errors',
        );
      }

      dispatchCustomEvent('refreshCompletePurchaseSection', {});
    }).catch((error) => {
      removeFullScreenLoader();
      document.getElementById('is-possible-placeholder').classList.add('error');
      dispatchCustomEvent('refreshCompletePurchaseSection', {});
      Drupal.logJavascriptError('apple-pay-checking-isPossible', error.message, 'payment errors');
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
    const controllerUrl = Drupal.url('checkoutcom/applepay/validate');
    const validationUrl = `${controllerUrl}?u=${event.validationURL}`;
    Axios.get(validationUrl).then((merchantSession) => {
      applePaySessionObject.completeMerchantValidation(merchantSession.data);
    }).catch((error) => {
      dispatchCustomEvent('spcCheckoutMessageUpdate', {
        type: 'error',
        message: getStringMessage('payment_error'),
      });
      removeFullScreenLoader();
      Drupal.logJavascriptError('apple-pay-merchant-validation', error.message, 'payment errors');
    });
  },

  onPaymentAuthorized: (event) => {
    const url = i18nMiddleWareUrl('payment/checkout-com-apple-pay/save');
    Axios.post(url, event.payment.token).then((response) => {
      if (response.data.success !== undefined && response.data.success === true) {
        // Update apple pay popup.
        applePaySessionObject.completePayment(window.ApplePaySession.STATUS_SUCCESS);

        // Place order now.
        placeOrder('checkout_com_applepay');
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
      Drupal.logJavascriptError('apple-pay-merchant-authorise', error.message, 'payment errors');
    });
  },

  onCancel: () => {
    removeFullScreenLoader();
    Drupal.logJavascriptError('apple-pay', 'user cancelled or error occurred', 'payment errors');
  },

  startPayment: (total) => {
    // Some features are not supported in some versions, this is browser
    // specific so done in JS.
    // Mada is supported only from version 5 onwards.
    let networks = drupalSettings.checkoutComApplePay.supportedNetworks.split(',');
    if (ApplePay.getApplePaySupportedVersion() < 5) {
      networks = networks.filter((element) => element !== 'mada');
    }

    // Prepare the parameters.
    const paymentRequest = {
      merchantIdentifier: drupalSettings.checkoutComApplePay.merchantIdentifier,
      currencyCode: drupalSettings.checkoutComApplePay.currencyCode,
      countryCode: drupalSettings.checkoutComApplePay.countryId,
      total: {
        label: drupalSettings.checkoutComApplePay.storeName,
        amount: total,
      },
      supportedNetworks: networks,
      merchantCapabilities: drupalSettings.checkoutComApplePay.merchantCapabilities.split(','),
    };

    // Start the payment session.
    try {
      applePaySessionObject = new window.ApplePaySession(1, paymentRequest);
      applePaySessionObject.onvalidatemerchant = ApplePay.onValidateMerchant;
      applePaySessionObject.onpaymentauthorized = ApplePay.onPaymentAuthorized;
      applePaySessionObject.oncancel = ApplePay.onCancel;
      applePaySessionObject.begin();
    } catch (e) {
      Drupal.logJavascriptError('Apple pay session error', e.message, 'payment errors');
      dispatchCustomEvent('spcCheckoutMessageUpdate', {
        type: 'error',
        message: getStringMessage('payment_error'),
      });
    }
  },
};

export default ApplePay;
