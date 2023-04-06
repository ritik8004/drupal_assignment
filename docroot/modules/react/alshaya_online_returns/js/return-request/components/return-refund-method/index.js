import React, { useState } from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import CardDetails from '../../../return-confirmation/components/card-details';
import EgiftCardDetails from '../egift-card-details';

const ReturnRefundMethod = ({
  paymentDetails, cardList, egiftCardType, isHybrid,
}) => {
  if (!hasValue(paymentDetails)) {
    return null;
  }

  const [selectedOption, setSelectedOption] = useState();
  const onOptionChange = () => {
    // For card details component radio button.
    const el = document.querySelector('.card-details input').value;
    setSelectedOption(el);
  };

  return (
    <>
      <div className="refund-method-wrapper">
        <div className="refund-method-title">
          { Drupal.t('Refund Method', {}, { context: 'online_returns' }) }
        </div>
        {cardList || egiftCardType
          ? (
            <div className="refund-method-listing">
              {isHybrid && (
                <>
                  <div className="hybrid-method-payment-msg">
                    { Drupal.t('Original Multiple payment methods used', {}, { context: 'online_returns' }) }
                  </div>
                  <div className="hybrid-method-list-msg">
                    { Drupal.t('Your refund will be credited back to the following payment methods.', {}, { context: 'online_returns' }) }
                  </div>
                </>
              )}
              <EgiftCardDetails
                cardList={cardList}
                selectedOption={selectedOption}
                egiftCardType={egiftCardType}
                paymentDetails={paymentDetails}
                isHybridPayment={isHybrid}
                setSelectedOption={setSelectedOption}
              />
              {/* For the payments made through COD, eGift and if there is multiple payment methods
              used i.e. hybrid we will not render the CardDetails component with radio button. */}
              {!hasValue(paymentDetails.cashondelivery)
                && !hasValue(paymentDetails.egift)
                && !isHybrid
                ? (
                  <>
                    <div className="method-wrapper card-details" onClick={() => onOptionChange('card-details')}>
                      <div className="method-wrapper">
                        <input
                          type="radio"
                          value="CardDetails"
                          name="CardPaymentDetails"
                          checked={selectedOption === 'CardDetails'}
                          className={selectedOption === 'CardDetails'}
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
                ) : (
                  <>
                    <div className="method-list-wrapper">
                      <div className="method-wrapper">
                        <CardDetails paymentDetails={paymentDetails} showCardIcon />
                      </div>
                    </div>
                    <div className="refund-message">
                      { Drupal.t('Estimated refund in 3-5 business days after we receive the item', {}, { context: 'online_returns' }) }
                    </div>
                  </>
                )}
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
