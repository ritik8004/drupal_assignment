(function (drupalSettings) {
  'use strict';
  window.postpayAsyncInit = function () {
    postpay.init({
      merchantId: drupalSettings.postpay.merchantId,
      sandbox: drupalSettings.postpay.sandbox,
      theme: drupalSettings.postpay.theme,
      locale: drupalSettings.postpay.locale
    });
  };

})(drupalSettings);
