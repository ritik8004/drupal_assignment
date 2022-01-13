import React from 'react';

import BillingInfo from '../billing-info';
import SectionTitle from '../../../utilities/section-title';
import {
  isBillingSameAsShippingInStorage, isDeliveryTypeSameAsInCart,
} from '../../../utilities/checkout_util';

export default class HDBillingAddress extends React.Component {
  isComponentMounted = false;

  constructor(props) {
    super(props);
    this.state = {
      shippingAsBilling: isBillingSameAsShippingInStorage(),
    };
  }

  componentDidMount() {
    this.isComponentMounted = true;
    const { shippingAsBilling } = this.state;
    if (isBillingSameAsShippingInStorage() !== shippingAsBilling) {
      this.setState({
        shippingAsBilling: isBillingSameAsShippingInStorage(),
      });
    }

    document.addEventListener('onBillingAddressUpdate', this.processBillingUpdate, false);
    document.addEventListener('onShippingAddressUpdate', this.processShippingUpdate, false);
  }

  componentWillUnmount() {
    this.isComponentMounted = false;
    document.removeEventListener('onBillingAddressUpdate', this.processBillingUpdate, false);
    document.removeEventListener('onShippingAddressUpdate', this.processShippingUpdate, false);
  }

  /**
   * Event handler for shipping update.
   */
  processShippingUpdate = (e) => {
    const data = e.detail;
    // If there is no error and update was fine, means user
    // has added billing address. We set in localstorage.
    if (data.error === undefined && isBillingSameAsShippingInStorage() && this.isComponentMounted) {
      this.setState({
        shippingAsBilling: true,
      });
    }
  };

  /**
   * Event handler for billing update.
   */
  processBillingUpdate = (e) => {
    const data = e.detail;
    // If there is no error and update was fine, means user
    // has changed the billing address. We set in localstorage.
    if (data.error === undefined && this.isComponentMounted) {
      if (data.cart !== undefined) {
        Drupal.addItemInLocalStorage('billing_shipping_same', false);
        this.setState({
          shippingAsBilling: false,
        });
      }
    }
  };

  isActive = () => {
    const { cart } = this.props;

    if (cart.cart.payment.methods === undefined || cart.cart.payment.methods.length === 0) {
      return false;
    }

    return isDeliveryTypeSameAsInCart(cart);
  };

  render() {
    const { cart, refreshCart } = this.props;
    const { shippingAsBilling } = this.state;

    // If carrier info not set on cart, means shipping is not
    // set. Thus billing is also not set and thus no need to
    // show billing info.
    if (cart.cart.shipping.method === null
      || cart.cart.billing_address === null
      || cart.cart.billing_address.city === 'NONE') {
      return (null);
    }

    // No need to show the billing address change for the
    // COD payment method.
    if (cart.cart.payment.method === undefined
      || cart.cart.payment.method === 'cashondelivery') {
      return (null);
    }

    const activeClass = this.isActive() ? 'active' : 'in-active';

    return (
      <div className={`spc-section-billing-address appear ${activeClass}`} style={{ animationDelay: '0.2s' }}>
        <SectionTitle>{Drupal.t('Billing address')}</SectionTitle>
        <div className="spc-billing-address-wrapper">
          <div className="spc-billing-bottom-panel">
            <BillingInfo
              cart={cart}
              refreshCart={refreshCart}
              shippingAsBilling={shippingAsBilling}
            />
          </div>
        </div>
      </div>
    );
  }
}
