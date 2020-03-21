import React from 'react';

const PriceElement = (props) => {
  const { amount: priceAmount } = props;
  if (typeof priceAmount === 'undefined') {
    return (null);
  }

  const { currency_config: currencyConfig } = window.drupalSettings.alshaya_spc;
  const amount = parseFloat(priceAmount);
  const priceParts = [
    (<span key="currency" className="price-currency suffix">{currencyConfig.currency_code}</span>),
    (<span key="amount" className="price-amount">{amount.toFixed(currencyConfig.decimal_points)}</span>),
  ];

  return (
    <span className="price-wrapper">
      <div className="price">
        {currencyConfig.currency_code_position === 'before' ? priceParts : priceParts.reverse()}
      </div>
    </span>
  );
};

export default PriceElement;
