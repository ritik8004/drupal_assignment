import React from 'react';
import { getAmountWithCurrency } from '../checkout_util';

const PriceElement = ({ amount: priceAmount }) => {
  if (typeof priceAmount === 'undefined') {
    return (null);
  }

  const priceParts = { ...getAmountWithCurrency(priceAmount, false) };
  const { currency_config: currencyConfig } = drupalSettings.alshaya_spc;

  priceParts.amount = (<span key="amount" style={{ display: 'inline-block' }} className="price-amount">{ parseFloat(priceParts.amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: currencyConfig.decimal_points }) }</span>);
  priceParts.currency = (<span key="currency" style={{ display: 'inline-block' }} className="price-currency suffix">{priceParts.currency}</span>);

  return (
    <span className="price-wrapper">
      <div className="price" dir="ltr">
        {Object.values(priceParts)}
      </div>
    </span>
  );
};

export default PriceElement;
