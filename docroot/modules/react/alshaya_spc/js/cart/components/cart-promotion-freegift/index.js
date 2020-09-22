import React, { useEffect } from 'react';
import { applyCode } from '../../../utilities/checkout_util';

const CartPromotionFreeGift = ({ promo, couponCode }) => {
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
        <span className="coupon-code" onClick={(e) => applyCode(e)}>{promo.coupon}</span>
        {Drupal.t('to get a Free Gift')}
        <span className="free-gift-title">
          <a
            className="use-ajax"
            data-dialog-type="modal"
            href={promo.promo_web_url}
          >
            {promo.promo_title}
          </a>
        </span>
      </div>
    </div>
  );
};

export default CartPromotionFreeGift;
