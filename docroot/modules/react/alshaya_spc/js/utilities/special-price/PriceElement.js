import React from 'react';
import { getAmountWithCurrency } from '../checkout_util';

const PriceElement = ({ amount: priceAmount }) => {
  if (typeof priceAmount === 'undefined') {
    return (null);
  }

  const priceParts = { ...getAmountWithCurrency(priceAmount, false) };
  priceParts.amount = (<span key="amount" style={{ display: 'inline-block' }} className="price-amount">{priceParts.amount}</span>);
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
