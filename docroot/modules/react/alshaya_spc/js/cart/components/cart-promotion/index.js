import React, { useEffect } from 'react';
import { applyCode } from '../../../utilities/checkout_util';

const CartPromotion = ({ promo, link, couponCode }) => {
  useEffect(() => {
    Drupal.ajax.bindAjaxLinks(document.body);
  }, [couponCode, promo]);

  if (promo.promo_web_url === undefined) {
    return (null);
  }

  if (promo.type === 'free_gift' && promo.coupon) {
    return (promo.coupon !== couponCode)
      ? (
        <div className="free-gift-promo">
          <div className="gift-message">
            {Drupal.t('Click')}
            <span className="coupon-code" onClick={(e) => applyCode(e)}>{promo.coupon}</span>
            {Drupal.t('to get a Free Gift')}
            <span className="free-gift-title">
              <a
                className="use-ajax"
                data-dialog-type="modal"
                href={Drupal.url(promo.promo_web_url)}
              >
                {promo.free_sku_title}
              </a>
            </span>
          </div>
        </div>
      ) : (null);
  }

  if (link) {
    return <span className="promotion-label"><a href={Drupal.url(promo.promo_web_url)}>{promo.text}</a></span>;
  }
  return <span className="promotion-label">{promo.text}</span>;
};

export default CartPromotion;
