import React from 'react';
import { hasValue } from '../../conditionsUtility';

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
  const { currency } = drupalSettings.reactTeaserView.price;

  const priceParts = [
    (<span key="currency" className={`${currencyClass} suffix`}>{currency}</span>),
    (<span key="amount" className={amountClass}>{price}</span>),
  ];

  // If we have fixed price then return the updated price markup.
  if (hasValue(fixedPrice)) {
    return (
      <div className={`price ${hasValue(currency) ? currency.toLowerCase() : ''}`} data-fp={fixedPrice}>
        <span className="price-wrapper">
          {drupalSettings.reactTeaserView.price.currencyPosition === 'before' ? priceParts : priceParts.reverse()}
        </span>
      </div>
    );
  }

  return (
    <span className="price-wrapper">
      <div className={`price ${hasValue(currency) ? currency.toLowerCase() : ''}`} data-fp={fixedPrice}>
        {drupalSettings.reactTeaserView.price.currencyPosition === 'before' ? priceParts : priceParts.reverse()}
      </div>
    </span>
  );
};

export default PriceElement;
