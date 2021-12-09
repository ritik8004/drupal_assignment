import React from 'react';
import TotalLineItem from '../../../utilities/total-line-item';

const EgiftCheckoutOrderSummary = (props) => {
  const { totals } = props;

  if (totals === undefined || totals === null) {
    return null;
  }

  const { egiftRedeemedAmount, balancePayable } = totals;

  if (egiftRedeemedAmount === undefined
    || balancePayable === undefined
    || egiftRedeemedAmount === 0) {
    return null;
  }

  return (
    <div className="egift-order-summary">
      <TotalLineItem
        name="paid-with-egift"
        title={Drupal.t('Paid With eGfit card')}
        value={egiftRedeemedAmount}
      />
      <TotalLineItem
        name="balance-payable"
        title={Drupal.t('Balance Payable')}
        value={balancePayable}
        showZeroValue
      />
    </div>
  );
};

export default EgiftCheckoutOrderSummary;
