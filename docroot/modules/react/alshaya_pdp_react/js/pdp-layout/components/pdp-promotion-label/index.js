import React from 'react';
import parse from 'html-react-parser';
import PdpDynamicPromotions from '../pdp-dynamic-promotions';

const PdpPromotionLabel = (props) => {
  const {
    skuItemCode, variantSelected, skuMainCode, cartDataValue,
  } = props;
  let { promotions } = drupalSettings.productInfo[skuItemCode];
  const { configurableCombinations } = drupalSettings;
  if (configurableCombinations) {
    promotions = drupalSettings.productInfo[skuItemCode].variants[variantSelected].promotions;
  }

  return (promotions) ? (
    <>
      <p>{parse(promotions)}</p>
      <div id="dynamic-promo-labels">
        <PdpDynamicPromotions skuMainCode={skuMainCode} cartDataValue={cartDataValue} />
      </div>
    </>
  ) : null;
};
export default PdpPromotionLabel;
