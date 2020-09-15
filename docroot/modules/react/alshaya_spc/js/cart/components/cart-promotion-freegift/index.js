import React, { useEffect } from 'react';
import { applyCode } from '../../../utilities/checkout_util';
import ConditionalView from '../../../common/components/conditional-view';

const CartPromotionFreeGift = ({ promo, couponCode }) => {
  useEffect(() => {
    Drupal.ajax.bindAjaxLinks(document.body);
  }, [couponCode, promo]);

  if (promo === undefined || promo.promo_web_url === undefined) {
    return (null);
  }

  return (
    <ConditionalView condition={promo.coupon !== couponCode}>
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
    </ConditionalView>
  );
};

export default CartPromotionFreeGift;
