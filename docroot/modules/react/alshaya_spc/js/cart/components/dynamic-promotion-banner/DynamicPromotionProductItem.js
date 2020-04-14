import React from 'react';

const DynamicPromotionProductItem = (props) => {
  const { dynamicPromoLabels } = props;
  if (dynamicPromoLabels === null) {
    return null;
  }

  const { link, label, promotion_nid: promotionNid } = dynamicPromoLabels;
  return (
    <a className="dynamic-promotion-link" href={link} data-promotion-nid={promotionNid}>{label}</a>
  );
};

export default DynamicPromotionProductItem;
