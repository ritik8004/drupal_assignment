import React from 'react';
import hmacSHA256 from 'crypto-js/hmac-sha256';
import Base64 from 'crypto-js/enc-base64';

class CompleteBenefitPayPayment extends React.Component {
  successCallback = () => {
    window.InApp.close();
  }

  errorCallback = () => {
    window.InApp.close();
  }

  // Create 'secure_hash' - the Hashing should be done by concatenating the
  // request parameters as key value pairs, sorting them ascending by key and
  // value, combining key and value with an =, then concatenating all the key-value
  // pairs separated by a comma and finally performing a SHA-256 hash using the
  // secret token with base64 encoding.
  calcSecureHash = (data) => {
    const { payment } = this.props;
    const benefitpayData = data;
    const sortedValues = Object.entries(benefitpayData)
      .filter((kv) => kv[1])
      .map((kv) => `${kv[0]}="${kv[1]}"`)
      .sort();
    const hashStr = sortedValues.join(',');
    const hash = hmacSHA256(hashStr, payment.benefitpaySecretKey);
    const hashInBase64 = Base64.stringify(hash);

    benefitpayData.hashedString = payment.benefitpaySecretKey;
    benefitpayData.secure_hash = hashInBase64;

    return benefitpayData;
  }

  prepareBenefitPayDetails = () => {
    const { payment, totals } = this.props;
    const {
      decimal_points: decimalPoints,
      currency_code: currencyCode,
    } = drupalSettings.alshaya_spc.currency_config;

    // Check if 'balancePayable' is set and has value greater than zero else
    // use the 'base_grand_total' variable to use as a transaction amount.
    // 'balancePayable' will be available in case or e-gift or aura methods are
    // used for the partial payment.
    const transactionAmount = (typeof totals.balancePayable !== 'undefined'
      && totals.balancePayable > 0)
      ? totals.balancePayable.toFixed(decimalPoints)
      : totals.base_grand_total.toFixed(decimalPoints);

    const data = {
      transactionAmount,
      transactionCurrency: currencyCode,
      referenceNumber: payment.referenceNumber,
      merchantId: payment.benefitpayMerchantId,
      appId: payment.benefitpayAppId,
    };

    return this.calcSecureHash(data);
  }

  loadBenefitPayModal = () => {
    const scriptExists = document.getElementById('benefit-pay-in-app');

    // Check if benefit pay script is already added on the page by
    // checking the element id added by the script.
    if (!scriptExists) {
      const inAppScript = document.createElement('script');
      inAppScript.async = true;
      const { environment } = drupalSettings.order_details.payment;
      inAppScript.src = `/modules/react/alshaya_spc/assets/js/${environment}/benefit_pay_in_app.min.js`;
      inAppScript.id = 'benefit-pay-in-app';
      document.body.appendChild(inAppScript);
      inAppScript.onload = () => {
        // Automatically open the Benefit Pay widget modal when user lands on the confirmation page.
        // We only want to auto open payment modal once so checking
        // `benefit_pay_modal_auto_opened` from storage to check if this is user's
        // first visit of confirmation page or user is reloading the page.
        if (typeof window.InApp !== 'undefined' && !Drupal.getItemFromLocalStorage('benefit_pay_modal_auto_opened')) {
          this.openInAppModal();
        }
      };
    }

    if (typeof window.InApp !== 'undefined') {
      this.openInAppModal();
    }
  }

  openInAppModal = () => {
    window.InApp.open(
      this.prepareBenefitPayDetails(),
      this.successCallback,
      this.errorCallback,
    );
    // Save that benefit pay modal was auto opened once.
    Drupal.addItemInLocalStorage('benefit_pay_modal_auto_opened', true);
  }

  render() {
    return (
      <div className="benefit-pay-container">
        <button
          type="button"
          ref={() => { this.loadBenefitPayModal(); }}
          onClick={() => { this.loadBenefitPayModal(); }}
          className="inapp-btn"
        />
      </div>
    );
  }
}

export default CompleteBenefitPayPayment;
