import React from 'react';
import PriceTagSVG from '../../../svg-component/price-tag-svg';

const DynamicPromotionBannerItem = (props) => {
  const {
    label,
    type,
    status,
    threshold,
    ruleId,
  } = props;

  if (status === 'inactive') {
    return (
      <div className={`promotion ${type}`} threshold={threshold} data-rule-id={ruleId}>
        <PriceTagSVG />
        <span className="promotion-text">{label}</span>
      </div>
    );
  }

  return (
    <div data-rule-id={ruleId} className={`promotion ${type}`}>
      { type === 'fixed_percentage_discount_order' && <PriceTagSVG /> }
      <span className="promotion-text">{label}</span>
    </div>
  );
};

export default DynamicPromotionBannerItem;
