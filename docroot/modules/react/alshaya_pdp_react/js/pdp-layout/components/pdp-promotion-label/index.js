import React from 'react';
import parse from 'html-react-parser';

const PdpPromotionLabel = (props) => {
  const { skuItemCode } = props;
  const { promotions } = drupalSettings.productInfo[skuItemCode];

  return (promotions) ? (
    <p>{parse(promotions)}</p>
  ) : null;
};
export default PdpPromotionLabel;
