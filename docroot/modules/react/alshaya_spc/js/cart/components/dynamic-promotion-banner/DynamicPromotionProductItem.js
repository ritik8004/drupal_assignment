import React from 'react';
import parse from 'html-react-parser';

const DynamicPromotionProductItem = ({ dynamicPromoLabels }) => {
  if (dynamicPromoLabels === null) {
    return null;
  }

  const { link, label, promotion_nid: promotionNid } = dynamicPromoLabels;
  return (
    <a className="dynamic-promotion-link" href={link} data-promotion-nid={promotionNid}>{parse(label)}</a>
  );
};

export default DynamicPromotionProductItem;
