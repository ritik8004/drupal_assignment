import React from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import CardDetails from '../../../return-confirmation/components/card-details';
import EgiftCardDetails from '../egift-card-details';

const ReturnRefundMethod = ({
  paymentDetails, cardList,
}) => {
  if (!hasValue(paymentDetails)) {
    return null;
  }

  return (
    <>
      <div className="refund-method-wrapper">
        <div className="refund-method-title">
          { Drupal.t('Refund Method', {}, { context: 'online_returns' }) }
        </div>
        {cardList
          ? (
            <div className="refund-method-listing">
              <EgiftCardDetails cardList={cardList} />
              <input type="radio" value="CardDetails" name="CardPaymentDetails" />
              <CardDetails paymentDetails={paymentDetails} showCardIcon />
              <div className="refund-message">
                { Drupal.t('Estimated refund in 3-5 business days after we receive the item', {}, { context: 'online_returns' }) }
              </div>
            </div>
          )
          : (
            <div className="refund-method-listing">
              <CardDetails paymentDetails={paymentDetails} showCardIcon />
              <div className="refund-message">
                { Drupal.t('Estimated refund in 3-5 business days after we receive the item', {}, { context: 'online_returns' }) }
              </div>
            </div>
          )}
      </div>
    </>
  );
};

export default ReturnRefundMethod;
