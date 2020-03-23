import React from 'react';
import PriceElement from '../../../utilities/special-price/PriceElement';

const MobileCartPreview = (props) => {
  const {
    totals: {
      free_delivery: freeDelivery,
      base_grand_total: baseGrandTotal,
    },
    total_items: totalItems,
  } = props;
  const totalText = freeDelivery
    ? Drupal.t('Total')
    : Drupal.t('Total (excluding delivery)');

  return (
    <>
      <div className="spc-mobile-cart-preview">
        <span className="cart-quantity">{Drupal.t('@total items', { '@total': totalItems })}</span>
        <span className="cart-text">{`${totalText} :`}</span>
        <span className="cart-value"><PriceElement amount={baseGrandTotal} /></span>
      </div>
    </>
  );
};

export default MobileCartPreview;
