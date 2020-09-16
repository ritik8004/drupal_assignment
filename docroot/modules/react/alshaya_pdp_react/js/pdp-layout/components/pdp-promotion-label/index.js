import React from 'react';
import PdpDynamicPromotions from '../pdp-dynamic-promotions';

const PdpPromotionLabel = (props) => {
  const {
    skuMainCode, cartDataValue, promotions,
  } = props;

  return (promotions) ? (
    <>
      {Object.keys(promotions).map((key) => (
        <p><a href={promotions[key].promo_web_url}>{promotions[key].text}</a></p>
      ))}
      <div id="dynamic-promo-labels">
        <PdpDynamicPromotions skuMainCode={skuMainCode} cartDataValue={cartDataValue} />
      </div>
    </>
  ) : null;
};
export default PdpPromotionLabel;
