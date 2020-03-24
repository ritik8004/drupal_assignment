import React from 'react';

const CartItemError = (props) => {
  const {
    errorMessage,
  } = props;

  // If we need to show error message.
  if (errorMessage !== null
    && errorMessage !== undefined
    && errorMessage.length > 0) {
    return (
      <div>
        {errorMessage}
      </div>
    );
  }
  return null;
};
export default CartItemError;
