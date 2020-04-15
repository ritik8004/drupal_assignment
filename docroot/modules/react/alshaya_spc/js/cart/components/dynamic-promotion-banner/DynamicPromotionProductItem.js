import React from 'react';

const DynamicPromotionProductItem = ({
  dynamicPromoLabels, dynamicPromoLabels: { link, label, promotion_nid: promotionNid } = {},
}) => (
  (dynamicPromoLabels === null)
    ? null
    : (
      <a className="dynamic-promotion-link" href={link} data-promotion-nid={promotionNid}>{label}</a>
    )
);

export default React.memo(DynamicPromotionProductItem);
