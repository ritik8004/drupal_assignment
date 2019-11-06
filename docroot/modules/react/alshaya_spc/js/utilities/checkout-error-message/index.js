import React from 'react';

export default class CheckoutErrorMessage extends React.Component {

  render() {
      return (
        <div className="spc-checkout-error-message-container">
          <div className="spc-checkout-error-message">
            {this.props.children}
          </div>
        </div>
      );
  }

}
