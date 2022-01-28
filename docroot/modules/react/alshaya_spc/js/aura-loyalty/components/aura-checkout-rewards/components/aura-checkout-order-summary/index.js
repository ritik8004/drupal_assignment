import React from 'react';
import TotalLineItem from '../../../../../utilities/total-line-item';
import DeliveryVATSuffix from '../../../../../utilities/delivery-vat-suffix';

const AuraCheckoutOrderSummary = (props) => {
  const {
    totals,
    shippingAmount,
    dontShowVatText,
    context,
  } = props;
  let balancePayableTitle = Drupal.t('Balance Payable');
  if (context === 'confirmation' || context === 'print') {
    // show amount paid title only in order confirmation page
    balancePayableTitle = Drupal.t('Amount Paid', {}, { context: 'egift' });
  }
  if (totals === undefined || totals === null) {
    return null;
  }

  const { paidWithAura, balancePayable } = totals;

  if (paidWithAura === undefined || balancePayable === undefined || paidWithAura === 0) {
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
          title={balancePayableTitle}
          value={balancePayable}
          showZeroValue
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
