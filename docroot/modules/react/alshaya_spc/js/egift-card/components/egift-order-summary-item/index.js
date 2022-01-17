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

  return null;
};

export default EgiftOrderSummaryItem;
