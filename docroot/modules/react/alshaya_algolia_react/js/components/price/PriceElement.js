import React from 'react';

const PriceElement = (props) => {
  const priceParts = [
    (<span className="price-currency suffix">{drupalSettings.reactTeaserView.price.currency}</span>),
    (<span className="price-amount">{props.amount.toFixed(drupalSettings.reactTeaserView.price.decimalPoints)}</span>)
  ];

  return (
    <span className="price-wrapper">
      <div className="price">
        {drupalSettings.reactTeaserView.price.currencyPosition == 'before' ? priceParts.map(item => item) : priceParts.reverse().map(item => item)}
      </div>
    </span>
  );
};

export default PriceElement;
