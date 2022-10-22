import React, { useState, useEffect } from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import CartNotificationDrawerContent from '../cart-notification-drawer-content';
import CartNotificationDrawerPopupContent from '../utilities/cart-notification-drawer-popup-content';

const CartNotificationDrawer = ({ productData }) => {
  if (!hasValue(productData)) {
    return null;
  }

  const [open, setCartDrawerState] = useState(true);

  useEffect(() => {
    setCartDrawerState(true);
  });

  const closeModal = () => {
    document.querySelector('body').classList.remove('overlay-cart-drawer');
    document.querySelector('body').classList.add('hide-minimalistic-header');
    setCartDrawerState(false);
  };

  if (!open) {
    return null;
  }

  const getPanelContent = (product) => {
    if (hasValue(product) && hasValue(product.productInfo)) {
      return (
        <CartNotificationDrawerContent
          productData={product.productInfo}
          closeModal={() => closeModal()}
          overlayClass="overlay-cart"
        />
      );
    }
    return null;
  };

  const panelContent = getPanelContent(productData);

  if (!hasValue(panelContent)) {
    return null;
  }

  return (
    <div className="cart-notification-drawer">
      <CartNotificationDrawerPopupContent>
        {panelContent}
      </CartNotificationDrawerPopupContent>
    </div>
  );
};

export default CartNotificationDrawer;
