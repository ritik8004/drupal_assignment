import React from 'react';

import SectionTitle from '../../../utilities/section-title';
import EmptyDeliveryText from '../empty-delivery';
import HomeDeliveryInfo from '../home-delivery';
import ClicknCollectDeiveryInfo from '../cnc-delivery';
import {
  isDeliveryTypeSameAsInCart,
} from '../../../utilities/checkout_util';
import {
  getCncSectionTitle,
} from '../../../utilities/cnc_util';

export default class DeliveryInformation extends React.Component {
  showEmpty = (cart) => {
    // Delivery method selected is not same as what set in cart.
    if (!isDeliveryTypeSameAsInCart(cart)) {
      return true;
    }

    const deliveryType = this.getDeliveryMethodToShow(cart);
    if (deliveryType === 'home_delivery' && cart.cart.shipping.address !== null) {
      return false;
    }
    if (deliveryType === 'click_and_collect' && typeof cart.cart.shipping.storeInfo !== 'undefined') {
      return false;
    }

    return true;
  };

  getDeliveryMethodToShow = (cart) => {
    if (typeof cart.delivery_type !== 'undefined') {
      return cart.delivery_type;
    }
    if (typeof cart.cart.shipping.type !== 'undefined') {
      return cart.cart.shipping.type;
    }
    return 'home_delivery';
  };

  render() {
    const {
      cart,
      refreshCart,
      isExpressDeliveryAvailable,
      shippingInfoUpdated,
    } = this.props;
    let title = '';
    if (cart.delivery_type !== undefined) {
      if (cart.delivery_type === 'click_and_collect') {
        title = getCncSectionTitle();
      } else {
        title = Drupal.t('delivery information');
      }
    }

    if (title.length === 0) {
      title = cart.cart.shipping.type === 'click_and_collect'
        ? getCncSectionTitle()
        : Drupal.t('delivery information');
    }

    const deliveryType = this.getDeliveryMethodToShow(cart);

    let deliveryComponent = null;
    if (this.showEmpty(cart)) {
      deliveryComponent = (
        <EmptyDeliveryText
          cart={cart}
          refreshCart={refreshCart}
          isExpressDeliveryAvailable={isExpressDeliveryAvailable}
        />
      );
    } else if (deliveryType === 'home_delivery') {
      deliveryComponent = (
        <HomeDeliveryInfo
          shippingInfoUpdated={shippingInfoUpdated}
          cart={cart}
          refreshCart={refreshCart}
          isExpressDeliveryAvailable={isExpressDeliveryAvailable}
        />
      );
    } else if (deliveryType === 'click_and_collect') {
      deliveryComponent = <ClicknCollectDeiveryInfo cart={cart} refreshCart={refreshCart} />;
    }

    // Removing any previously added error messages
    // from complete purchase validation.
    const deliveryInfoError = document.getElementById('delivery-information-error');
    if (deliveryInfoError !== null) {
      deliveryInfoError.remove();
    }

    return (
      <div className="spc-checkout-delivery-information fadeInUp" style={{ animationDelay: '0.45s' }}>
        <SectionTitle>{title}</SectionTitle>
        {deliveryComponent}
      </div>
    );
  }
}
