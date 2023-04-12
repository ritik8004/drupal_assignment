import React, { useEffect } from 'react';

import { selectFreeGift, openCartFreeGiftModal, getCartFreeGiftModalId } from '../../../utilities/free_gift_util';

const CartPromotionFreeGift = ({
  promo,
  couponCode,
}) => {
  useEffect(() => {
    Drupal.ajax.bindAjaxLinks(document.body);
  }, [couponCode, promo]);

  if (promo === undefined
    || promo.promo_web_url === undefined
    || promo.promo_web_url === null
    || promo.coupon === undefined
    || promo.coupon === null
    || promo.coupon.length === 0
    || promo.coupon === couponCode) {
    return (null);
  }

  return (
    <div className="free-gift-promo">
      <div className="gift-message">
        {Drupal.t('Click')}
        <span className="coupon-code" onClick={() => selectFreeGift(promo.coupon, promo['#free_sku_code'], promo['#free_sku_type'], promo['#promo_type'])}>{promo.coupon}</span>
        {Drupal.t('to get a Free Gift')}
        <span className="free-gift-title" onClick={() => openCartFreeGiftModal(promo['#free_sku_code'])}>
          {promo.promo_title}
        </span>
        <a
          id={getCartFreeGiftModalId(promo['#free_sku_code'])}
          className="use-ajax visually-hidden"
          data-sku={promo['#free_sku_code']}
          data-promo-rule-id={promo.promoRuleId}
          data-dialog-type="modal"
          href={promo.promo_web_url}
        />
      </div>
    </div>
  );
};

export default CartPromotionFreeGift;
