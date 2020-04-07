import React from 'react';

const FreeDeliveryText = (props) => {
  const { freeDelivery, text } = props;
  if (!freeDelivery) {
    return <span className="delivery-prefix">{text}</span>;
  }

  return <span className="delivery-prefix" />;
};

export default FreeDeliveryText;
