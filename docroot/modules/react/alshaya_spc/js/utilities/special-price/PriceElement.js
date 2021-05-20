import React from 'react';
import { getAmountWithCurrency } from '../checkout_util';

const PriceElement = ({ amount: priceAmount, format }) => {
  if (typeof priceAmount === 'undefined') {
    return (null);
  }

  const priceParts = { ...getAmountWithCurrency(priceAmount, false) };
  const { currency_config: currencyConfig } = drupalSettings.alshaya_spc;

  if (format === 'string') {
    return (`${priceParts.currency} ${priceParts.amount}`);
  }

  priceParts.amount = (<span key="amount" style={{ display: 'inline-block' }} className="price-amount">{ parseFloat(priceParts.amount).toLocaleString(undefined, { minimumFractionDigits: currencyConfig.decimal_points, maximumFractionDigits: currencyConfig.decimal_points }) }</span>);
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
