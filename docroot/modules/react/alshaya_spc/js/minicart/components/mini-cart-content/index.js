import React from 'react';
import PriceElement from '../../../utilities/special-price/PriceElement';

const MiniCartContent = (props) => {
  const { amount, qty } = props;
  return (
    <>
      <a className="cart-link-total" href={Drupal.url('cart')}>
        <PriceElement amount={amount} />
      </a>
      <a className="cart-link" href={Drupal.url('cart')}>
        <span className="quantity">{qty}</span>
      </a>
    </>
  );
};

export default MiniCartContent;
