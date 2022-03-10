import React from 'react';

const EgiftOrderSummaryItem = (props) => {
  const {
    orderDetails,
  } = props;
  // If order has any egift card.
  if (orderDetails.giftCardRecieptEmail !== undefined) {
    return (
      <div className="spc-order-summary-item order-summary-item fadeInUp redeem">
        <span className="spc-egift-label spc-label">{Drupal.t('eGift Card To:', {}, { context: 'egift' })}</span>
        <span className="spc-value spc-egift-value-wrapper">
          <span className="spc-egift-value spc-egift-mail-value">
            {orderDetails.giftCardRecieptEmail.join(', ')}
          </span>
          <span className="spc-egift-value">
            {Drupal.t('eGift card will be sent immediately', {}, { context: 'egift' })}
          </span>
        </span>
      </div>
    );
  }
  // If order has any topup card.
  if (orderDetails.isTopUp !== undefined) {
    return (
      <div className="spc-order-summary-item order-summary-item fadeInUp redeem">
        <span className="spc-topup-label spc-label">{Drupal.t('Top up to:', {}, { context: 'egift' })}</span>
        <span className="spc-value spc-topup-value-wrapper">
          <span className="spc-egift-value spc-egift-mail-value">
            {orderDetails.topUpRecieptEmail}
          </span>
          <span className="spc-topup-value">
            {Drupal.t('Top up amount will reflect immediately in card', {}, { context: 'egift' })}
          </span>
        </span>
      </div>
    );
  }

  return null;
};

export default EgiftOrderSummaryItem;
