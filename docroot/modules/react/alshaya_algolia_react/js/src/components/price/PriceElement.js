import React from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

const PriceItem = ({ amount, maxAmount }) => {
  const { decimalPoints } = drupalSettings.reactTeaserView.price;

  if (hasValue(maxAmount)) {
    return (
      <span key="amount" className="price-amount">
        {Number(amount).toFixed(decimalPoints)}
        <span className="min-max-separator">-</span>
        {Number(maxAmount).toFixed(decimalPoints)}
      </span>
    );
  } else {
    return (
      <span key="amount" className="price-amount">
      {amount.toFixed(decimalPoints)}
      </span>
    );
  }
};

const PriceElement = ({ amount, maxAmount }) => {
  if (typeof amount === 'undefined') {
    return (null);
  }

  const priceParts = [
    (
      <span key="currency" className="price-currency suffix">{drupalSettings.reactTeaserView.price.currency}</span>
    ),
    (<PriceItem key="price-item" amount={amount} maxAmount={maxAmount} />),
  ];

  return (
    <span className="price-wrapper">
      <div className="price">
        {drupalSettings.reactTeaserView.price.currencyPosition === 'before' ? priceParts : priceParts.reverse()}
      </div>
    </span>
  );
};

export default PriceElement;
