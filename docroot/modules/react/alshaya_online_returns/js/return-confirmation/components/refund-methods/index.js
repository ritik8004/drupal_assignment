import React from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import CardDetails from '../card-details';
import EgiftCardDetails from '../../../return-request/components/egift-card-details';

const RefundMethods = ({
  paymentInfo,
}) => {
  if (!hasValue(paymentInfo)) {
    return null;
  }
  // Fetching eGift card details from local storage.
  const egiftInStorage = Drupal.getItemFromLocalStorage('egift_card_details');
  if (hasValue(egiftInStorage)) {
    Drupal.removeItemFromLocalStorage('egift_card_details');
  }
  // Fetching eGift card type i.e. new card or not from local storage.
  const egiftCardType = Drupal.getItemFromLocalStorage('egift_card_type');
  if (egiftCardType) {
    Drupal.removeItemFromLocalStorage('egift_card_type');
  }

  return (
    <>
      <div className="refund-method-wrapper">
        <div className="refund-method-title light">
          { Drupal.t('Refund Method', {}, { context: 'online_returns' }) }
        </div>
        {egiftInStorage || egiftCardType
          ? (
            <EgiftCardDetails
              cardList={egiftInStorage}
              selectedOption={null}
              egiftCardType={egiftCardType}
              paymentDetails={paymentInfo}
            />
          )
          : (
            <CardDetails paymentDetails={paymentInfo} />
          )}
      </div>
    </>
  );
};

export default RefundMethods;
