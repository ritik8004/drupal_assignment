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
  const egiftInStorage = Drupal.getItemFromLocalStorage('egift_card_details');
  if (hasValue(egiftInStorage)) {
    Drupal.removeItemFromLocalStorage('egift_card_details');
  }

  return (
    <>
      <div className="refund-method-wrapper">
        <div className="refund-method-title light">
          { Drupal.t('Refund Method', {}, { context: 'online_returns' }) }
        </div>
        {egiftInStorage
          ? (
            <EgiftCardDetails
              cardList={egiftInStorage}
              selectedOption={null}
              egiftCardType={null}
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
