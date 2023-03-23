import React from 'react';

const PriceElement = ({
  amount,
  currencyClass = 'price-currency',
  amountClass = 'price-amount',
  fixedPrice = '',
}) => {
  if (typeof amount === 'undefined') {
    return (null);
  }

  // If the price is string, render as is, else process it.
  const price = (typeof amount !== 'string') ? amount.toFixed(drupalSettings.reactTeaserView.price.decimalPoints) : amount;

  const priceParts = [
    (<span key="currency" className={`${currencyClass} suffix`}>{drupalSettings.reactTeaserView.price.currency}</span>),
    (<span key="amount" className={amountClass}>{price}</span>),
  ];

  return (
    <span className="price-wrapper">
      <div className="price" data-fp={fixedPrice}>
        {drupalSettings.reactTeaserView.price.currencyPosition === 'before' ? priceParts : priceParts.reverse()}
      </div>
    </span>
  );
};

export default PriceElement;
