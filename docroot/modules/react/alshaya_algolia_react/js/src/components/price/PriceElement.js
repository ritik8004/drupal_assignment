import React from 'react';

const PriceElement = (props) => {
  if (typeof props.amount === 'undefined') {
    return (null);
  }

  const priceParts = [
    (<span key="currency" className="price-currency suffix">{drupalSettings.reactTeaserView.price.currency}</span>),
    (<span key="amount" className="price-amount">{props.amount.toFixed(drupalSettings.reactTeaserView.price.decimalPoints)}</span>)
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
