import React from 'react';

export default class CheckoutMessage extends React.Component {

  render() {
      const type = this.props.type;
      return (
        <div className={"spc-checkout-" + type + "-message-container"}>
          <div className={"spc-checkout-" + type + "-message"}>
            {this.props.children}
          </div>
        </div>
      );
  }

}
