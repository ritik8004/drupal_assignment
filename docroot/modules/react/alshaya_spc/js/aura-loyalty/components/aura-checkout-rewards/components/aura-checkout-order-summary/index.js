import React from 'react';
import TotalLineItem from '../../../../../utilities/total-line-item';

const AuraCheckoutOrderSummary = (props) => {
  const { totals } = props;

  if (totals === undefined || totals === null) {
    return null;
  }

  const { paidWithAura, balancePayable } = totals;

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

export default AuraCheckoutOrderSummary;
