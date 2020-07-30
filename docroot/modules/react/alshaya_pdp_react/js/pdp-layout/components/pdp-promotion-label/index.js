import React from 'react';
import parse from 'html-react-parser';

const PdpPromotionLabel = (props) => {
  const { skuItemCode } = props;
  const { promotions } = drupalSettings.productInfo[skuItemCode];

  return (
    <p>{parse(promotions)}</p>
  );
};
export default PdpPromotionLabel;
