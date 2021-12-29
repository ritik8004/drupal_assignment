import React from 'react';
import TotalLineItem from '../../../utilities/total-line-item';

const EgiftCheckoutOrderSummary = (props) => {
  const { totals } = props;

  if (totals === undefined || totals === null) {
    return null;
  }

  const { egiftRedeemedAmount, balancePayable } = totals;

  // If we dont have egiftRedeemedAmount and eGiftbalancePayable dont show egift-order-summary.
  if (egiftRedeemedAmount === undefined // Redeem Amount entered by user.
    || balancePayable === undefined // Balance payable remaining amount to be paid.
    || egiftRedeemedAmount === 0) {
    return null;
  }

  return (
    <div className="egift-order-summary">
      <TotalLineItem
        name="paid-with-egift"
        title={Drupal.t('Paid With eGfit card', {}, { context: 'egift' })}
        value={egiftRedeemedAmount}
      />
      <TotalLineItem
        name="balance-payable"
        title={Drupal.t('Balance Payable', {}, { context: 'egift' })}
        value={balancePayable}
        showZeroValue
      />
    </div>
  );
};

export default EgiftCheckoutOrderSummary;
