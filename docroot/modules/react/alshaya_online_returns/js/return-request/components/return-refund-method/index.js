import React, { useState } from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import CardDetails from '../../../return-confirmation/components/card-details';
import EgiftCardDetails from '../egift-card-details';

const ReturnRefundMethod = ({
  paymentDetails, cardList, egiftCardType,
}) => {
  if (!hasValue(paymentDetails)) {
    return null;
  }

  const [selectedOption, setSelectedOption] = useState();
  const onOptionChange = (e) => {
    setSelectedOption(e.target.value);
  };

  return (
    <>
      <div className="refund-method-wrapper">
        <div className="refund-method-title">
          { Drupal.t('Refund Method', {}, { context: 'online_returns' }) }
        </div>
        {cardList || egiftCardType
          ? (
            <div className="refund-method-listing" onClick={onOptionChange}>
              <EgiftCardDetails
                cardList={cardList}
                selectedOption={selectedOption}
                egiftCardType={egiftCardType}
              />
              {!hasValue(paymentDetails.cashondelivery)
                ? (
                  <>
                    <div className="method-list-wrapper">
                      <div className="method-wrapper">
                        <input
                          type="radio"
                          value="CardDetails"
                          name="CardPaymentDetails"
                          checked={selectedOption === 'CardDetails'}
                        />
                        <label className="radio-sim radio-label">
                          <CardDetails paymentDetails={paymentDetails} showCardIcon />
                        </label>
                      </div>
                    </div>
                    <div className="refund-message">
                      { Drupal.t('Estimated refund in 3-5 business days after we receive the item', {}, { context: 'online_returns' }) }
                    </div>
                  </>
                ) : (<> </>)}
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
