import React from 'react';

export default class Price extends React.Component {

  render() {
    const currency_config = window.drupalSettings.alshaya_spc.currency_config;
    const price = this.props.price.toFixed(currency_config.decimal_points);
    return <span>{currency_config.currency_code} {price}</span>
  }

}
