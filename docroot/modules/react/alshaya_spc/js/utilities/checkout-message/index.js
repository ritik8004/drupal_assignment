import React from 'react';

export default class CheckoutMessage extends React.Component {
  render() {
    const { type } = this.props;
    if (this.props.children) {
      return (
        <div className={`spc-messages-container spc-checkout-${type}-message-container`}>
          <div className={`spc-message spc-checkout-${type}-message`}>
            {this.props.children}
          </div>
        </div>
      );
    }
    else {
      return (
        <div className={`spc-messages-container spc-checkout-${type}-message-container`}/>
      );
    }
  }
}
