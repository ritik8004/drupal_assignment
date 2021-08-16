import React from 'react';
import QRCode from 'react-qr-code';
import DeviceView from '../../../common/components/device-view';
import PriceElement from '../../../utilities/special-price/PriceElement';

const CompleteBenefitPayPayment = (props) => {
  const { payment, totals } = props;

  return (
    <div className="benefit-pay-container">
      <DeviceView device="mobile">
        <button
          type="button"
          className="inapp-btn"
        >
          {Drupal.t('Pay using Benefit Pay App')}
        </button>
        <div className="qr">
          <QRCode value={payment.qrData} />
        </div>
      </DeviceView>
      <DeviceView device="above-mobile">
        <div className="title">{Drupal.t('Please complete your payment by scanning the QR code.')}</div>
        <div className="qr-code-wrapper">
          <div className="qr-left">
            <span className="">{Drupal.t('Scan to Pay')}</span>
            <QRCode value={payment.qrData} />
          </div>
          <div className="info-right">
            <div>
              <span className="spc-label">
                {Drupal.t('Merchant')}
                :
              </span>
              <span className="spc-value">{Drupal.t('Alshaya')}</span>
            </div>
            <div>
              <span className="spc-label">
                {Drupal.t('Amount')}
                :
              </span>
              <PriceElement amount={totals.base_grand_total} format="string" />
            </div>
            <div>
              <span className="spc-label">
                {Drupal.t('Reference number')}
                :
              </span>
              <span className="spc-value">{payment.referenceNumber}</span>
            </div>
          </div>
        </div>
      </DeviceView>
    </div>
  );
};

export default CompleteBenefitPayPayment;
