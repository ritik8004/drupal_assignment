import React from 'react';

const PriceElement = ({ amount, maxAmount }) => {
  if (typeof amount === 'undefined') {
    return (null);
  }
  const { decimalPoints } = drupalSettings.reactTeaserView.price;
  const priceItem = (typeof maxAmount !== 'undefined')
    ? (
      <span key="amount" className="price-amount">
        {amount.toFixed(decimalPoints)}
        <span className="min-max-separator">-</span>
        {maxAmount.toFixed(decimalPoints)}
      </span>
    )
    : (
      <span key="amount" className="price-amount">{amount.toFixed(decimalPoints)}</span>
    );

  const priceParts = [
    (
      <span key="currency" className="price-currency suffix">{drupalSettings.reactTeaserView.price.currency}</span>
    ),
    (priceItem),
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
