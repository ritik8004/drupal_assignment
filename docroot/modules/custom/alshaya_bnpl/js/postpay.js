(function (drupalSettings) {
  window.postpayAsyncInit = function () {
    if (!postpay || !drupalSettings.postpay) {
      return;
    }

    postpay.init({
      merchantId: drupalSettings.postpay.merchant_id,
      sandbox: drupalSettings.postpay.sandbox,
      theme: drupalSettings.postpay.theme,
      locale: drupalSettings.postpay.locale
    });

    // Dispatch the event to perform some action on the initiation of the Postpay.
    var postpayInit = new CustomEvent('alshayaPostpayInit', {bubbles: true});
    document.dispatchEvent(postpayInit);
  };
})(drupalSettings);
