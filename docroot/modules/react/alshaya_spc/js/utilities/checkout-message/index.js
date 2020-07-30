import React from 'react';

const CheckoutMessage = (props) => {
  const { type, children, context } = props;

  if (children) {
    return (
      <div className={`spc-messages-container ${context} spc-checkout-${type}-message-container`}>
        <div className={`spc-message spc-checkout-${type}-message`}>
          {children}
        </div>
      </div>
    );
  }

  return (
    <div className={`spc-messages-container ${context} spc-checkout-${type}-message-container`} />
  );
};

export default CheckoutMessage;
