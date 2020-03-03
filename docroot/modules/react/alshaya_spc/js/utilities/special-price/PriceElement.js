import React from 'react';

export default class PriceElement extends React.Component {
  render() {
    if (typeof this.props.amount === 'undefined') {
      return (null);
    }

    const { currency_config } = window.drupalSettings.alshaya_spc;
    const amount = parseFloat(this.props.amount);
    const priceParts = [
      (<span key="currency" className="price-currency suffix">{currency_config.currency_code}</span>),
      (<span key="amount" className="price-amount">{amount.toFixed(currency_config.decimal_points)}</span>),
    ];

    return (
      <span className="price-wrapper">
        <div className="price">
          {currency_config.currency_code_position === 'before' ? priceParts : priceParts.reverse()}
        </div>
      </span>
    );
  }
}
