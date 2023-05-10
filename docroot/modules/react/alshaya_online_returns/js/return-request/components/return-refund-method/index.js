import React, { useState } from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import CardDetails from '../../../return-confirmation/components/card-details';
import EgiftCardDetails from '../egift-card-details';
import { isUserAuthenticated } from '../../../../../js/utilities/helper';
import { isEgiftRefundEnabled } from '../../../../../js/utilities/util';

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

  let differentEgiftCard = false;
  if (isUserAuthenticated() && isEgiftRefundEnabled()) {
    // Logic to decide whether the payment made through the eGift card
    // is linked to the user account or a different one which is not linked to the user.
    if (paymentDetails.egift && hasValue(paymentDetails.egift.card_number)
      && cardList && hasValue(cardList.card_number) && !isHybrid) {
      // Fetching the last 4 digits of the linked eGift card.
      const lastFourChar = cardList.card_number.substring(
        cardList.card_number.length - 4,
      );
      // Checking whether the payment is made through same linked eGift or not.
      if (lastFourChar !== paymentDetails.egift.card_number) {
        differentEgiftCard = true;
      }
    }
  }

  const HybridCardDetailsComponent = () => (isHybrid
    ? (
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
    )
    : (
      <></>
    ));

  // Custom function for card details component to avoid the nested ternary expressions.
  const CardDetailsComponent = () => (!hasValue(paymentDetails.cashondelivery)
    && !hasValue(paymentDetails.egift) && !differentEgiftCard && !isHybrid
    ? (
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
    )
    : (
      <HybridCardDetailsComponent />
    ));

  return (
    <>
      <div className="refund-method-wrapper">
        <div className="refund-method-title">
          { Drupal.t('Refund Method', {}, { context: 'online_returns' }) }
        </div>
        {isEgiftRefundEnabled() && (cardList || egiftCardType)
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
                  <CardDetailsComponent />
                )}
            </div>
          )
          : (
            <div className="refund-method-listing hybrid-payment">
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
