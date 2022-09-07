import React from 'react';
import CartDrawerPopupContent from '../utilities/cart-drawer-popup-content';

const CartDrawerPanel = (props) => {
  const {
    panelContent,
  } = props;

  return (
    <div className="cart-drawer-popup-panel">
      <CartDrawerPopupContent>
        {panelContent}
      </CartDrawerPopupContent>
    </div>
  );
};

export default CartDrawerPanel;
