import React from 'react';
import { getAmountWithCurrency } from '../checkout_util';
import { hasValue } from '../../../../js/utilities/conditionsUtility';

const PriceElement = ({ amount: priceAmount, format }) => {
  if (!hasValue(priceAmount)
    || parseFloat(priceAmount).toFixed(2) === '0.00') {
    return (null);
  }

  // If price amount contains any alphabet or Arabic character, render as is.
  if (Number.isNaN(parseFloat(priceAmount))
    && !/\d/.test(priceAmount)) {
    return priceAmount;
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
