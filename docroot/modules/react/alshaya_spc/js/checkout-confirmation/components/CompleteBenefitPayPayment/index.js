import React from 'react';
import QRCode from 'react-qr-code';
import hmacSHA256 from 'crypto-js/hmac-sha256';
import Base64 from 'crypto-js/enc-base64';
import DeviceView from '../../../common/components/device-view';
import PriceElement from '../../../utilities/special-price/PriceElement';
import BenefitPaySVG from '../../../svg-component/payment-method-svg/components/benefit-pay-svg';

class CompleteBenefitPayPayment extends React.Component {
  appId = 2264812781;

  merchantId = 4187951;

  secretKey = 'vbgm3o5354c820vhrj0ld5wck693yipbabf43nq9m6avr';

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
    const benefitpayData = data;
    const sortedValues = Object.entries(benefitpayData)
      .filter((kv) => kv[1])
      .map((kv) => `${kv[0]}="${kv[1]}"`)
      .sort();
    const hashStr = sortedValues.join(',');
    const hash = hmacSHA256(hashStr, this.secretKey);
    const hashInBase64 = Base64.stringify(hash);

    benefitpayData.hashedString = this.secretKey;
    benefitpayData.secure_hash = hashInBase64;

    return benefitpayData;
  }

  prepareBenefitPayDetails = () => {
    const { payment, totals } = this.props;

    const data = {
      transactionAmount: totals.base_grand_total.toFixed(
        drupalSettings.alshaya_spc.currency_config.decimal_points,
      ),
      transactionCurrency: drupalSettings.alshaya_spc.currency_config.currency_code,
      referenceNumber: payment.referenceNumber,
      merchantId: this.merchantId,
      appId: this.appId,
    };

    return this.calcSecureHash(data);
  }

  loadBenefitPayModal = () => {
    const scriptExists = document.getElementById('benefit-pay-in-app');

    if (!scriptExists) {
      const inAppScript = document.createElement('script');
      inAppScript.async = true;
      inAppScript.src = '/modules/react/alshaya_spc/assets/js/benefit_pay_in_app.min.js';
      inAppScript.id = 'benefit-pay-in-app';
      document.head.appendChild(inAppScript);
      inAppScript.onload = () => {
        if (typeof InApp !== 'undefined') {
          this.openInAppModal();
        }
      };
    }

    if (typeof InApp !== 'undefined') {
      this.openInAppModal();
    }
  }

  openInAppModal = () => {
    window.InApp.open(
      this.prepareBenefitPayDetails(),
      this.successCallback,
      this.errorCallback,
    );
  }

  render() {
    const { payment, totals } = this.props;

    return (
      <div className="benefit-pay-container">
        <DeviceView device="mobile">
          <button
            type="button"
            ref={() => { this.loadBenefitPayModal(); }}
            onClick={() => { this.loadBenefitPayModal(); }}
            className="inapp-btn"
          />
        </DeviceView>
        <DeviceView device="above-mobile">
          <div className="title">{Drupal.t('Please complete your payment by scanning the QR code.')}</div>
          <div className="benefit-pay-wrapper">
            <div className="benefit-pay-content">
              <div className="benefit-pay-header">
                <BenefitPaySVG />
                <div className="title">{Drupal.t('BenefitPay')}</div>
              </div>
              <div className="qr-code-wrapper">
                <div className="qr-left">
                  <span>{Drupal.t('Scan to Pay')}</span>
                  <QRCode value={payment.qrData} size="100" />
                </div>
                <div className="info-right">
                  <div>
                    <span className="spc-label">
                      {Drupal.t('Merchant')}
                    </span>
                    <span className="spc-value merchant-value">{Drupal.t('Alshaya')}</span>
                  </div>
                  <div>
                    <span className="spc-label">
                      {Drupal.t('Amount')}
                    </span>
                    <span className="spc-value">
                      <PriceElement amount={totals.base_grand_total} format="string" />
                    </span>
                  </div>
                  <div>
                    <span className="spc-label">
                      {Drupal.t('Reference number')}
                    </span>
                    <span className="spc-value">{payment.referenceNumber}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </DeviceView>
      </div>
    );
  }
}

export default CompleteBenefitPayPayment;
