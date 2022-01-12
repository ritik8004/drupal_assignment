import React from 'react';

const EgiftOrderSummaryItem = (props) => {
  const {
    orderDetails,
  } = props;
  // If order has any egift card.
  if (orderDetails.giftCardRecieptEmail !== undefined) {
    return (
      <div className="spc-order-summary-item order-summary-item fadeInUp redeem">
        <span className="spc-egift-label">{Drupal.t('eGift card to:', {}, { context: 'egift' })}</span>
        <span className="spc-egift-value always-ltr">
          {orderDetails.giftCardRecieptEmail.join(' , ')}
        </span>
        <span className="spc-egift-value always-ltr">
          {Drupal.t('eGift card will be sent immediately', {}, { context: 'egift' })}
        </span>
      </div>
    );
  }
  // If order has any topup card.
  if (orderDetails.topupRecieptEmail !== undefined) {
    return (
      <div className="spc-order-summary-item order-summary-item fadeInUp redeem">
        <span className="spc-topup-label">{Drupal.t('Top-up to:', {}, { context: 'egift' })}</span>
        <span className="spc-topup-value always-ltr">
          {orderDetails.topupRecieptEmail}
        </span>
        <span className="spc-topup-value always-ltr">
          {Drupal.t('Top-up amount will reflect immediately in card', {}, { context: 'egift' })}
        </span>
      </div>
    );
  }

  return null;
};

export default EgiftOrderSummaryItem;
