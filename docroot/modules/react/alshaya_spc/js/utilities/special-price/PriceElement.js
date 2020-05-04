import React from 'react';
import { getAmountWithCurrency } from '../checkout_util';

const PriceElement = ({ amount: priceAmount }) => {
  if (typeof priceAmount === 'undefined') {
    return (null);
  }

  const amountWithCurrency = getAmountWithCurrency(priceAmount, false);
  const priceParts = Object.keys(amountWithCurrency).map((key) => {
    if (key === 'amount') {
      return (<span key="amount" className="price-amount">{amountWithCurrency[key]}</span>);
    }
    return (<span key="currency" className="price-currency suffix">{amountWithCurrency[key]}</span>);
  });

  return (
    <span className="price-wrapper">
      <div className="price">
        {priceParts}
      </div>
    </span>
  );
};

export default PriceElement;
