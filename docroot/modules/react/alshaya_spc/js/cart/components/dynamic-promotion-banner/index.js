import React from 'react';
import DynamicPromotionBannerItem from './DynamicPromotionBannerItem';

const DynamicPromotionBanner = (props) => {
  const { dynamicPromoLabelsCart } = props;

  if (dynamicPromoLabelsCart === null) {
    return '';
  }

  const activePromotionBanners = [];
  const inactivePromotionBanners = [];
  // Active Promotions.
  Object.values(dynamicPromoLabelsCart.qualified).forEach((message) => {
    const { rule_id: ruleId, label, type } = message;
    activePromotionBanners.push(
      <DynamicPromotionBannerItem
        status="active"
        key={ruleId}
        ruleId={ruleId}
        label={label}
        type={type}
      />,
    );
  });

  // In-Active Promotions.
  if (dynamicPromoLabelsCart.next_eligible !== undefined
    && dynamicPromoLabelsCart.next_eligible.type !== undefined) {
    const {
      label,
      type,
      rule_id: ruleId,
      threshold_reached: thresholdReached,
    } = dynamicPromoLabelsCart.next_eligible;
    inactivePromotionBanners.push(
      <DynamicPromotionBannerItem
        status="inactive"
        key={ruleId}
        ruleId={ruleId}
        label={label}
        type={type}
        threshold={thresholdReached.toString()}
      />,
    );
  }

  return (
    <div className="dynamic-promotion-wrapper">
      <div className="active-promotions">{ activePromotionBanners }</div>
      <div className="inactive-promotions">{ inactivePromotionBanners }</div>
    </div>
  );
};

export default DynamicPromotionBanner;
