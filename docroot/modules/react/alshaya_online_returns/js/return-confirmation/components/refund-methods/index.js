import React, { useState } from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import CardDetails from '../card-details';
import EgiftCardDetails from '../../../return-request/components/egift-card-details';
import { isEgiftRefundEnabled, isHybridPayment } from '../../../../../js/utilities/util';
import { callEgiftApi } from '../../../../../js/utilities/egiftCardHelper';
import { isUserAuthenticated } from '../../../../../js/utilities/helper';

const RefundMethods = ({
  paymentInfo,
}) => {
  const [cardList, setCardList] = useState(null);
  // Variable to check whether the new eGift option needs
  // to be given to the user or not in the refund form.
  const [showNewEgiftCardOption, setNewEgiftCardOption] = useState(false);
  if (!hasValue(paymentInfo)) {
    return null;
  }

  // Checking whether the eGift refund feature is enabled or not and the user is authenticated.
  if (isUserAuthenticated() && isEgiftRefundEnabled() && !hasValue(paymentInfo.aura)) {
    // Call to get customer linked eGift card details.
    const result = callEgiftApi('eGiftCardList', 'GET', {});
    if (result instanceof Promise) {
      result.then((response) => {
        if (hasValue(response.data) && hasValue(response.data.card_number)) {
          const cardData = response.data ? response.data : null;
          setCardList(cardData);
        } else {
          // If user has no linked eGift card, we call api to get all
          // eGift cards having same email address as of the current user.
          const unlinkedResult = callEgiftApi('unlinkedEiftCardList', 'GET', {});
          unlinkedResult.then((unlinkresponse) => {
            if (typeof unlinkresponse.data === 'undefined'
              || !hasValue(unlinkresponse.data.card_number)
              || (typeof paymentInfo.cashondelivery !== 'undefined'
                && hasValue(paymentInfo.cashondelivery.payment_type)
                && paymentInfo.cashondelivery.payment_type === 'cashondelivery')) {
              setNewEgiftCardOption(true);
            }
          });
        }
      });
    }
  }

  // Variable to check whether the payment made through multiple methods i.e. hybrid or not.
  const isHybrid = isHybridPayment(paymentInfo);
  // If the eGift refund feature is not enabled, there is no eGift card details API called
  // and the payment is not made through AURA (as for AURA we don't used to call the API)
  // we will not render any egift components.
  if (isEgiftRefundEnabled() && !isHybrid && !cardList && !hasValue(paymentInfo.aura)
    && !showNewEgiftCardOption) {
    return null;
  }

  // Fetching the value from local storage to know whether
  // eGift card was selected or not in refund form.
  const isEgiftCardSelected = Drupal.getItemFromLocalStorage('is_egift_selected');
  // Assigning payment data to a different variable to make the change on that conditionally,
  // otherwise it will throw the "no-param-reassign" lint error.
  const paymentData = paymentInfo;
  // Deleting the eGift value from the payment object
  // if it is hybrid, as we are already showing the new eGift option here.
  if (isEgiftRefundEnabled() && isHybrid && hasValue(paymentInfo.egift)) {
    delete paymentData.egift;
  }
  // Deleting the aura_payment value from the payment object
  // if we have both aura and aura_payment keys in the payment object,
  // as we only need to render the data present in aura.
  if (hasValue(paymentData.aura) && hasValue(paymentData.aura_payment)) {
    delete paymentData.aura_payment;
  }

  // Components for eGift card single payment method.
  // For eGift refund feature if the user selected eGift as an option in the refund form,
  // and the payment is made through COD or eGift we will show the EgiftCardDetails component.
  const SinglePaymentMethod = () => (isEgiftRefundEnabled()
    && ((isEgiftCardSelected && (cardList || showNewEgiftCardOption))
    || paymentData.cashondelivery || paymentData.egift)
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
  const HybridPaymentMethods = () => (isEgiftRefundEnabled() && !hasValue(paymentInfo.aura)
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
