import React from 'react';
import { isUserAuthenticated } from '../../../../../js/utilities/helper';
import SectionTitle from '../../../utilities/section-title';

const CartDrawerContent = (props) => {
  const {
    productData,
    closeModal,
  } = props;

  let checkoutUrl = Drupal.url('cart/login');
  if (isUserAuthenticated()) {
    checkoutUrl = Drupal.url('checkout');
  }
  return (
    <div className="cart-drawer-content">
      <div className="cart-drawer-section">
        <div className="title-block">
          <SectionTitle>{Drupal.t('ADDED TO YOUR BAG')}</SectionTitle>
          <a className="close-modal" onClick={closeModal} />
        </div>
        <div className="product-details">
          <div className="product-img">
            <img loading="lazy" src={productData.image} alt={productData.product_name} title={productData.product_name} />
          </div>
          <div className="product-desc">
            <div className="product-title">{Drupal.t('@productTitle has been added to your bag.', { '@productTitle': productData.product_name })}</div>
            <div className="product-quantity">{Drupal.t('Qty : @quantity', { '@quantity': productData.quantity })}</div>
          </div>
        </div>
        <div className="cart-drawer-links">
          <a href={Drupal.url('cart')} className="add-to-bag">{Drupal.t('VIEW BAG')}</a>
          <a href={checkoutUrl} className="checkout">{Drupal.t('CHECKOUT')}</a>
        </div>
      </div>
    </div>
  );
};

export default CartDrawerContent;
