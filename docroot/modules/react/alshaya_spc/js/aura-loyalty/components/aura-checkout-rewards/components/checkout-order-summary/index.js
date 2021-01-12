import React from 'react';
import TotalLineItem from '../../../../../utilities/total-line-item';

const CheckoutOrderSummary = (props) => {
  const { loyaltyPaymentData } = props;

  if (loyaltyPaymentData === undefined || loyaltyPaymentData === null) {
    return null;
  }

  const { paidWithAura, balancePayable } = loyaltyPaymentData;

  if (paidWithAura === null || balancePayable === null) {
    return null;
  }

  return (
    <div className="aura-order-summary">
      <TotalLineItem
        name="paid-with-aura"
        title={Drupal.t('Paid With Aura')}
        value={paidWithAura}
      />
      <TotalLineItem
        name="balance-payable"
        title={Drupal.t('Balance Payable')}
        value={balancePayable}
      />
    </div>
  );
};

export default CheckoutOrderSummary;
