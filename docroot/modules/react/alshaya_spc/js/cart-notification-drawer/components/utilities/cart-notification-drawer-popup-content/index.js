import React from 'react';

const CartNotificationDrawerPopupContent = ({ children, className = '' }) => {
  const closeModal = (e) => {
    if (e.target.classList.contains('cart-drawer-popup-content')) {
      if (document.querySelector('body').classList.contains('overlay-cart-drawer')) {
        document.querySelector('body').classList.remove('overlay-cart-drawer');
      }

      if (children !== null && document.querySelector('body').classList.contains(children.props.overlayClass)) {
        children.props.closeModal();
      }
    }
  };

  return (
    <div
      className={`cart-drawer-popup-content ${className}`}
      onClick={closeModal}
    >
      {children}
    </div>
  );
};

export default CartNotificationDrawerPopupContent;