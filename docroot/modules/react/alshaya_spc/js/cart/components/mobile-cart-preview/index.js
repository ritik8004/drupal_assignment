import React from 'react';
import PriceElement from '../../../utilities/special-price/PriceElement';

const MobileCartPreview = (props) => {
  const { totals: { free_delivery, base_grand_total }, total_items } = props;
  const totalText = free_delivery
    ? Drupal.t('Total')
    : Drupal.t('Total (excluding delivery)');

  return (
    <>
      <div className="spc-mobile-cart-preview">
        <span className="cart-quantity">{Drupal.t('@qty items', { '@qty': total_items })}</span>
        <span className="cart-text">{`${totalText} :`}</span>
        <span className="cart-value"><PriceElement amount={base_grand_total} /></span>
      </div>
    </>
  );
};

export default MobileCartPreview;
