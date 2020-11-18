import React from 'react';
import parse from 'html-react-parser';
import ConditionalView from '../../../common/components/conditional-view';

const PdpFreeGift = ({
  freeGiftImage, 
  freeGiftTitle, 
  freeGiftPromoCode, 
  freeGiftPromoUrl,
  freeGiftMessage,
}) => (
  <div className="free-gift-promotions free-gift-promotions-full-view-mode">
    <div className="free-gift-promo-wrapper free-gift-promo-list">
      <div className="free-gift-image">
        <img 
          src={freeGiftImage['#url']} 
          alt={freeGiftImage['#alt']} 
          title={freeGiftImage['#title']} 
          typeof="foaf:Image" 
          className="b-lazy b-loaded height-sync-processed" 
        />
      </div>
      <div className="free-gift-wrapper">
        <div className="free-gift-title">
          {Drupal.t('Free Gift')}
        </div>
        <div className="free-gift-message">
          {parse(freeGiftMessage)}
        </div>
        <div class="free-gift-coupon-code">
          {Drupal.t('Use code ')}
          <span class="coupon-code">{freeGiftPromoCode}</span>
          {Drupal.t('in basket')}
        </div>
      </div>
    </div>
  </div>
);

export default PdpFreeGift;
