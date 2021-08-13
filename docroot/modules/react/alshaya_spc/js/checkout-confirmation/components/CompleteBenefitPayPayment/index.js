import React from 'react';
import QRCode from 'react-qr-code';
import DeviceView from '../../../common/components/device-view';
import PriceElement from '../../../utilities/special-price/PriceElement';
import BenefitPaySVG from '../../../svg-component/payment-method-svg/components/benefit-pay-svg';

const CompleteBenefitPayPayment = (props) => {
  const { payment, totals } = props;

  return (
    <div className="benefit-pay-container">
      <DeviceView device="mobile">
        <button
          type="button"
          className="inapp-btn"
        >
          {Drupal.t('Pay using BenefitPay App')}
        </button>
        <div className="qr">
          <div><span className="qr-label">{Drupal.t('or Scan to Pay')}</span></div>
          <QRCode value={payment.qrData} size='130' />
        </div>
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
                <QRCode value={payment.qrData} size='100' />
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
            <div className="benefit-pay-footer"><a href="/">{Drupal.t('How to pay using BenefitPay?')}</a></div>
          </div>
        </div>
      </DeviceView>
    </div>
  );
};

export default CompleteBenefitPayPayment;
