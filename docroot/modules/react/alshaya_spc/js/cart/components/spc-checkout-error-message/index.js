import React from 'react';

export default class CartErrorMessage extends React.Component {

  render() {
    const in_stock = this.props.in_stock;
     if (in_stock === false) {
      return (
        <div className="spc-checkout-error-message-container">
          <div className="spc-checkout-error-message">
            {this.props.children}
          </div>
        </div>
      );
    }

    return (null);
  }

}
