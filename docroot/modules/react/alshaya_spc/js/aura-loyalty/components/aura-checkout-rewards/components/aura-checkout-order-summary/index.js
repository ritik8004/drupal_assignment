import React from 'react';
import TotalLineItem from '../../../../../utilities/total-line-item';
import VatText from '../../../../../utilities/vat-text';

const AuraCheckoutOrderSummary = (props) => {
  const { totals } = props;

  if (totals === undefined || totals === null) {
    return null;
  }

  const { paidWithAura, balancePayable } = totals;

  if (paidWithAura === undefined || balancePayable === undefined) {
    return null;
  }

  if (paidWithAura <= 0 || balancePayable <= 0) {
    return null;
  }

  return (
    <div className="aura-order-summary">
      <TotalLineItem
        name="paid-with-aura"
        title={Drupal.t('Paid With Aura')}
        value={paidWithAura}
      />
      <div className="hero-total aura-hero-total">
        <TotalLineItem
          name="balance-payable"
          title={Drupal.t('Balance Payable')}
          value={balancePayable}
        />
        <VatText />
      </div>
    </div>
  );
};

export default AuraCheckoutOrderSummary;
