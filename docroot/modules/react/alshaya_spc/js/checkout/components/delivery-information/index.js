import React from 'react';

import SectionTitle from '../../../utilities/section-title';
import EmptyDeliveryText from '../empty-delivery';
import HomeDeliveryInfo from '../home-delivery';
import ClicknCollectDeiveryInfo from '../cnc-delivery';
import {
  isDeliveryTypeSameAsInCart,
} from '../../../utilities/checkout_util';

export default class DeliveryInformation extends React.Component {
  constructor(props) {
    super(props);
    const { cart } = this.props;
    this.state = {
      showEmpty: this.showEmpty(cart),
    };
  }

  componentWillReceiveProps(nextProps) {
    this.setState({
      showEmpty: this.showEmpty(nextProps.cart),
    });
  }

  showEmpty = (cart) => {
    // Delivery method selected is not same as what set in cart.
    if (!isDeliveryTypeSameAsInCart(cart)) {
      return true;
    }

    const deliveryType = this.getDeliveryMethodToShow(cart);
    if (deliveryType === 'hd' && cart.cart.shipping_address !== null) {
      return false;
    } if (deliveryType === 'cnc' && typeof cart.cart.store_info !== 'undefined') {
      return false;
    }
    return true;
  }

  getDeliveryMethodToShow = (cart) => {
    if (typeof cart.delivery_type !== 'undefined') {
      return cart.delivery_type;
    }
    if (typeof cart.cart.delivery_type !== 'undefined') {
      return cart.cart.delivery_type;
    }

    return 'hd';
  }

  render() {
    const { cart, refreshCart } = this.props;

    let title = '';
    if (cart.delivery_type !== undefined) {
      if (cart.delivery_type === 'cnc') {
        title = Drupal.t('Collection Store');
      } else {
        title = Drupal.t('delivery information');
      }
    }

    if (title.length === 0) {
      title = cart.cart.delivery_type === 'cnc'
        ? Drupal.t('Collection Store')
        : Drupal.t('delivery information');
    }

    const deliveryType = this.getDeliveryMethodToShow(cart);

    let deliveryComponent = null;
    if (this.showEmpty(cart)) {
      deliveryComponent = <EmptyDeliveryText cart={cart} refreshCart={refreshCart} />;
    } else if (deliveryType === 'hd') {
      deliveryComponent = <HomeDeliveryInfo cart={cart} refreshCart={refreshCart} />;
    } else if (deliveryType === 'cnc') {
      deliveryComponent = <ClicknCollectDeiveryInfo cart={cart} refreshCart={refreshCart} />;
    }

    return (
      <div className="spc-checkout-delivery-information">
        <SectionTitle>{title}</SectionTitle>
        {deliveryComponent}
      </div>
    );
  }
}
