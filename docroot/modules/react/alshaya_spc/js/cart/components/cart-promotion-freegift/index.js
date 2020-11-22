import React, { useEffect } from 'react';

const CartPromotionFreeGift = ({
  promo,
  couponCode,
  selectFreeGift,
  openCartFreeGiftModal,
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
        <span className="free-gift-title" onClick={(e) => openCartFreeGiftModal(e, promo.coupon, promo['#free_sku_code'], promo['#free_sku_type'])}>
          {promo.promo_title}
        </span>
        <a
          id="spc-free-gift"
          className="use-ajax visually-hidden"
          data-dialog-type="modal"
          href={promo.promo_web_url}
        />
      </div>
    </div>
  );
};

export default CartPromotionFreeGift;
