import React from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import CardDetails from '../card-details';
import EgiftCardDetails from '../../../return-request/components/egift-card-details';
import { isEgiftRefundEnabled, isHybridPayment } from '../../../../../js/utilities/util';
import { callEgiftApi } from '../../../../../js/utilities/egiftCardHelper';
import { isUserAuthenticated } from '../../../../../js/utilities/helper';

const RefundMethods = ({
  paymentInfo,
}) => {
  if (!hasValue(paymentInfo)) {
    return null;
  }

  const cardList = {};
  let egiftCardType = false;
  // Checking whether the eGift refund feature is enabled or not and the user is authenticated.
  if (isUserAuthenticated() && isEgiftRefundEnabled() && !hasValue(paymentInfo.aura)) {
    // Call to get customer linked eGift card details.
    const result = callEgiftApi('eGiftCardList', 'GET', {});
    if (result instanceof Promise) {
      result.then((response) => {
        if (hasValue(response.data) && hasValue(response.data.card_number)) {
          const cardData = response.data ? response.data : null;
          Object.assign(cardList, cardData);
        } else {
          // Call to get un-linked eGift card details associated with the user email.
          const unlinkedResult = callEgiftApi('unlinkedEiftCardList', 'GET', {});
          unlinkedResult.then((unlinkresponse) => {
            if (!hasValue(unlinkresponse.data.card_list)
              || (hasValue(paymentInfo.cashondelivery.payment_type)
                && paymentInfo.cashondelivery.payment_type === 'cashondelivery')) {
              egiftCardType = true;
            }
          });
        }
      });
    }
  }

  // Variable to check whether the payment made through multiple methods i.e. hybrid or not.
  const isHybrid = isHybridPayment(paymentInfo);
  // Assigning payment data to a different variable to make the change on that conditionally,
  // otherwise it will throw the "no-param-reassign" lint error.
  const paymentData = paymentInfo;
  // Deleting the eGift value from the payment object
  // if it is hybrid, as we are already showing the new eGift option here.
  if (isHybrid && hasValue(paymentInfo.egift)) {
    delete paymentData.egift;
  }
  // Components for eGift card single payment method.
  const SinglePaymentMethod = () => ((cardList || egiftCardType)
    ? (
      <>
        <EgiftCardDetails
          cardList={cardList}
          selectedOption={null}
          egiftCardType={egiftCardType}
          paymentDetails={paymentData}
        />
      </>
    )
    : (
      <CardDetails paymentDetails={paymentData} showCardIcon />
    ));

  // Components for eGift card hybrid payment method.
  const HybridPaymentMethods = () => ((cardList || egiftCardType) && !hasValue(paymentInfo.aura)
    ? (
      <>
        <EgiftCardDetails
          cardList={cardList}
          selectedOption={null}
          egiftCardType={egiftCardType}
          paymentDetails={paymentData}
        />
        <CardDetails paymentDetails={paymentData} showCardIcon />
      </>
    )
    : (
      <CardDetails paymentDetails={paymentData} showCardIcon />
    ));

  return (
    <>
      <div className="refund-method-wrapper">
        <div className="refund-method-title light">
          { Drupal.t('Refund Method', {}, { context: 'online_returns' }) }
        </div>
        {isHybrid
          ? (
            <HybridPaymentMethods />
          ) : (
            <SinglePaymentMethod />
          )}
      </div>
    </>
  );
};

export default RefundMethods;
