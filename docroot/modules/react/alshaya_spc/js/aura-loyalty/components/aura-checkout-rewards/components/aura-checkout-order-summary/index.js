import React from 'react';
import TotalLineItem from '../../../../../utilities/total-line-item';
import DeliveryVATSuffix from '../../../../../utilities/delivery-vat-suffix';

const AuraCheckoutOrderSummary = (props) => {
  const { totals, shippingAmount, dontShowVatText } = props;

  if (totals === undefined || totals === null) {
    return null;
  }

  const { paidWithAura, balancePayable } = totals;

  if (paidWithAura === undefined || balancePayable === undefined) {
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
        <DeliveryVATSuffix
          shippingAmount={shippingAmount}
          dontShowVatText={dontShowVatText}
        />
      </div>
    </div>
  );
};

export default AuraCheckoutOrderSummary;
