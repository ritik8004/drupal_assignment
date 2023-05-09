import React from 'react';
import parse from 'html-react-parser';
import ConditionalView from '../../../common/components/conditional-view';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

const PdpFreeGift = ({
  freeGiftImage,
  freeGiftPromoCode,
  freeGiftMessage,
  freeGiftPromoType,
  freeGiftTitle,
}) => {
  if (freeGiftPromoType === 'FREE_GIFT_SUB_TYPE_ONE_SKU') {
    return (
      <>
        <div className="free-gift-promotions free-gift-promotions-full-view-mode">
          <div className="free-gift-promo-wrapper free-gift-promo-list">
            <ConditionalView condition={hasValue(freeGiftImage)}>
              <div className="free-gift-image">
                <img
                  src={freeGiftImage['#url']}
                  alt={freeGiftImage['#alt']}
                  title={freeGiftImage['#title']}
                  typeof="foaf:Image"
                />
              </div>
            </ConditionalView>
            <div className="free-gift-wrapper">
              <div className="free-gift-title">
                {Drupal.t('Free Gift')}
              </div>
              <div className="free-gift-message">
                {parse(freeGiftMessage)}
              </div>
              <ConditionalView condition={hasValue(freeGiftPromoCode)}>
                <div className="free-gift-coupon-code">
                  {Drupal.t('Use code ')}
                  <span className="coupon-code">{freeGiftPromoCode}</span>
                  {Drupal.t('in basket')}
                </div>
              </ConditionalView>
            </div>
          </div>
        </div>
      </>
    );
  }

  const freeGiftImageMarkup = freeGiftImage
    ? (
      <div className="free-gift-image">
        {parse(freeGiftImage)}
      </div>
    )
    : null;

  return (
    <>
      <div className="free-gift-promotions free-gift-promotions-full-view-mode">
        <div className="free-gift-promo-wrapper free-gift-promo-list">
          {freeGiftImageMarkup}
          <div className="free-gift-wrapper">
            <div className="free-gift-title">
              {Drupal.t('Free Gift')}
            </div>
            <div className="free-gift-message">
              {parse(freeGiftTitle)}
              {' '}
              {Drupal.t('with this product')}
            </div>
            <ConditionalView condition={hasValue(freeGiftPromoCode)}>
              <div className="free-gift-coupon-code">
                {Drupal.t('Use code')}
                {freeGiftPromoCode.map((code, i) => (
                  <span className="coupon-code" key={i.toString()}>{ code.value }</span>
                ))}
                {Drupal.t('with this product')}
              </div>
            </ConditionalView>
          </div>
        </div>
      </div>
    </>
  );
};

export default PdpFreeGift;
