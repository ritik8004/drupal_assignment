import React from 'react';

export default class Price extends React.Component {

  render() {
    const currency_config = window.drupalSettings.alshaya_spc.currency_config;
    const price = this.props.price.toFixed(currency_config.decimal_points);

    return <div className="price-type__wrapper">
      <div className="price">
        <span className="price-currency suffix">{currency_config.currency_code}</span>
        <span className="price-amount">{ price }</span>
      </div>
    </div>
  }

}
