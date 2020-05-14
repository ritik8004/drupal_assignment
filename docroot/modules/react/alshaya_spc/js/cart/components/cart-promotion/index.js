import React from 'react';

const CartPromotion = (props) => {
  const { promo, link } = props;
  if (promo.promo_web_url === undefined) {
    return (null);
  }
  if (link) {
    return <span className="promotion-label"><a href={Drupal.url(promo.promo_web_url)}>{promo.text}</a></span>;
  }
  return <span className="promotion-label">{promo.text}</span>;
};

export default CartPromotion;
