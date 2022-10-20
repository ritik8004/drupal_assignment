import React from 'react';
import parse from 'html-react-parser';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import { isUserAuthenticated } from '../../../../../js/utilities/helper';
import SectionTitle from '../../../utilities/section-title';

const CartNotificationDrawerContent = (props) => {
  const {
    productData,
    closeModal,
  } = props;

  if (!hasValue(productData)) {
    return null;
  }

  const checkoutUrl = isUserAuthenticated() ? Drupal.url('checkout') : Drupal.url('cart/login');
  return (
    <div className="cart-drawer-content">
      <div className="title-block">
        <SectionTitle>{Drupal.t('ADDED TO YOUR BAG')}</SectionTitle>
        <a className="close-modal" onClick={closeModal} />
      </div>
      <div className="product-details">
        <div className="product-img">
          <img loading="lazy" src={productData.image} alt={productData.product_name} title={productData.product_name} />
        </div>
        <div className="product-desc">
          <div className="product-title">{parse(Drupal.t('@productTitle has been added to your bag.', { '@productTitle': productData.product_name }))}</div>
          <div className="product-quantity">{Drupal.t('Qty : @quantity', { '@quantity': productData.quantity })}</div>
        </div>
      </div>
      <div className="cart-drawer-links">
        <a href={Drupal.url('cart')} className="add-to-bag">{Drupal.t('VIEW BAG')}</a>
        <a href={checkoutUrl} className="checkout">{Drupal.t('CHECKOUT')}</a>
      </div>
      <div className="dy-cart-recommendations">
        {/* use below container for cross-sell / up-sell type product carousel. */}
        <div id="cart-crossell-upsell-wrapper" data-sku={productData.sku} />

        {/* use below container for recommendation type product carousel */}
        <div id="cart-recommended-products-wrapper" data-sku={productData.sku} />

        {/* use below container for additional recommendations */}
        <div id="cart-additional-recommendations-products-wrapper" data-sku={productData.sku} />
      </div>
    </div>
  );
};

export default CartNotificationDrawerContent;
