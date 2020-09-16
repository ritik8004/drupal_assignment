import React from 'react';
import parse from 'html-react-parser';
import ConditionalView from '../../../common/components/conditional-view';

const PdpFreeGift = ({
  freeGiftImage, freeGiftTitle, freeGiftPromoCode,
}) => (
  <div className="free-gift-promotions free-gift-promotions-full-view-mode">
    <div className="free-gift-promo-wrapper free-gift-promo-list">
      <div className="free-gift-image">
        {parse(freeGiftImage)}
      </div>
      <div className="free-gift-wrapper">
        <div className="free-gift-title">
          {Drupal.t('Free Gift')}
        </div>
        <div className="free-gift-message">
          {parse(freeGiftTitle)}
          {' '}
          {Drupal.t('with this product')}
        </div>
        <ConditionalView condition={freeGiftPromoCode.length > 0}>
          <div className="free-gift-coupon-code">
            {Drupal.t('Use Code')}
            {freeGiftPromoCode.map((code, i) => (
              <span className="coupon-code" key={i.toString()}>{ code.value }</span>
            ))}
            {Drupal.t('with this product')}
          </div>
        </ConditionalView>
      </div>
    </div>
  </div>
);

export default PdpFreeGift;
