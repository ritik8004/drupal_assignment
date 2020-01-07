import React from 'react';

export default class CheckoutConfigurableOption extends React.Component {

  render() {
    const { label, value } = this.props.label;

    return (
      <React.Fragment>
        <div className="spc-cart-product-attribute">
          <span className="spc-cart-product-attribute-label">{label + ': '}</span>
          <span className="spc-cart-product-attribute-value">{value}</span>
        </div>
      </React.Fragment>
    );
  }

}
