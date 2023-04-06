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

  // Egift card details linked with the user.
  const cardList = {};
  // Variable to check whether the new eGift option needs
  // to be given to the user or not in the refund form.
  let showNewEgiftCardOption = false;
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
          // If user has no linked eGift card, we call api to get all
          // eGift cards having same email address as of the current user.
          const unlinkedResult = callEgiftApi('unlinkedEiftCardList', 'GET', {});
          unlinkedResult.then((unlinkresponse) => {
            if (!hasValue(unlinkresponse.data.card_list)
              || (hasValue(paymentInfo.cashondelivery.payment_type)
                && paymentInfo.cashondelivery.payment_type === 'cashondelivery')) {
              showNewEgiftCardOption = true;
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
  const SinglePaymentMethod = () => ((cardList || showNewEgiftCardOption)
    ? (
      <>
        <EgiftCardDetails
          cardList={cardList}
          selectedOption={null}
          egiftCardType={showNewEgiftCardOption}
          paymentDetails={paymentData}
        />
      </>
    )
    : (
      <CardDetails paymentDetails={paymentData} showCardIcon />
    ));

  // Components for eGift card hybrid payment method.
  const HybridPaymentMethods = () => ((cardList || showNewEgiftCardOption)
  && !hasValue(paymentInfo.aura)
    ? (
      <>
        <EgiftCardDetails
          cardList={cardList}
          selectedOption={null}
          egiftCardType={showNewEgiftCardOption}
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
        {/* If the order is made through multiple payments or hybrid, we are rendering the
          HybridPaymentMethods component else the SinglePaymentMethod component. */}
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
