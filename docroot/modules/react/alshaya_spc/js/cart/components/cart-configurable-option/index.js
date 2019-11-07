import React from 'react';

export default class CartConfigurableOption extends React.Component {

  render() {
    return (
      <React.Fragment>
        <div className="spc-cart-product-attribute">
          <span className="spc-cart-product-attribute-label">{this.props.label.label + ': '}</span>
          <span className="spc-cart-product-attribute-value">{this.props.label.value}</span>
        </div>
      </React.Fragment>
    );
  }

}
