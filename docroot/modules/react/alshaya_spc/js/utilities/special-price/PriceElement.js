import React from 'react';
import { getAmountWithCurrency } from '../checkout_util';

const PriceElement = ({ amount: priceAmount }) => {
  if (typeof priceAmount === 'undefined') {
    return (null);
  }

  const priceParts = { ...getAmountWithCurrency(priceAmount, false) };
  priceParts.amount = (<span key="amount" className="price-amount">{priceParts.amount}</span>);
  priceParts.currency = (<span key="currency" className="price-currency suffix">{priceParts.currency}</span>);

  return (
    <span className="price-wrapper">
      <div className="price">
        {Object.values(priceParts)}
      </div>
    </span>
  );
};

export default PriceElement;
