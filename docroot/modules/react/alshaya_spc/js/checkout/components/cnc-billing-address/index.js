import React from 'react';

import Popup from 'reactjs-popup';
import Loading from '../../../utilities/loading';
import BillingInfo from '../billing-info';
import SectionTitle from '../../../utilities/section-title';
import {
  processBillingUpdateFromForm,
  getAddressPopupClassName,
} from '../../../utilities/checkout_address_process';
import {
  isBillingSameAsShippingInStorage,
} from '../../../utilities/checkout_util';

const AddressContent = React.lazy(() => import('../address-popup-content'));

// Storage key for billing shipping info same or not.
const localStorageKey = 'billing_shipping_same';

export default class CnCBillingAddress extends React.Component {
  isComponentMounted = false;

  constructor(props) {
    super(props);
    this.state = {
      open: false,
      shippingAsBilling: isBillingSameAsShippingInStorage(),
    };
  }

  componentDidMount() {
    this.isComponentMounted = true;
    document.addEventListener('onBillingAddressUpdate', this.processBillingUpdate, false);
  }

  componentWillUnmount() {
    this.isComponentMounted = false;
    document.removeEventListener('onBillingAddressUpdate', this.processBillingUpdate, false);
  }

  showPopup = () => {
    this.setState({
      open: true,
    });
  };

  closePopup = () => {
    this.setState({
      open: false,
    });
  };

  /**
   * Event handler for billing update.
   */
  processBillingUpdate = (e) => {
    if (this.isComponentMounted) {
      const data = e.detail;
      const { refreshCart } = this.props;

      // If there is no error and update was fine, means user
      // has changed the billing address. We set in localstorage.
      if (data.error === undefined) {
        if (data.cart !== undefined) {
          localStorage.setItem(localStorageKey, false);
          this.setState({
            shippingAsBilling: false,
          });
        }
      }

      // Refresh cart.
      refreshCart(data);
      // Close modal.
      this.closePopup();
    }
  };

  /**
   * Process address form submission.
   */
  processAddress = (e) => {
    const { cart } = this.props;
    return processBillingUpdateFromForm(e, cart.cart.shipping_address);
  }

  /**
   * Message to show when billing is
   * same as shipping.
   */
  sameBillingAsShippingMessage = () => Drupal.t('We have set your billing address same as delivery address. You can select a different one by clicking the change button above.');

  render() {
    const { cart, refreshCart } = this.props;
    const { open, shippingAsBilling } = this.state;

    // If carrier info not set, means shipping info not set.
    // So we don't need to show bulling.
    if (cart.cart.carrier_info === undefined
      || cart.cart.carrier_info === null) {
      return (null);
    }

    // If billing address city value is 'NONE',
    // means its default billing address (same as shipping)
    // and not added by the user.
    let billingAddressAddedByUser = true;
    if (cart.cart.billing_address.city === 'NONE') {
      billingAddressAddedByUser = false;
    }

    const shippingAddress = cart.cart.shipping_address;
    const editAddressData = {
      static: {
        fullname: `${shippingAddress.firstname} ${shippingAddress.lastname}`,
        telephone: shippingAddress.telephone,
      },
    };

    // If user has not added billing address.
    if (!billingAddressAddedByUser) {
      return (
        <div className="spc-section-billing-address cnc-flow">
          <SectionTitle>{Drupal.t('billing address')}</SectionTitle>
          <div className="spc-billing-address-wrapper">
            <div className="spc-billing-top-panel spc-billing-cc-panel" onClick={(e) => this.showPopup(e)}>
              {Drupal.t('please add your billing address.')}
            </div>
            <Popup
              className={getAddressPopupClassName()}
              open={open}
              onClose={this.closePopup}
              closeOnDocumentClick={false}
            >
              <React.Suspense fallback={<Loading />}>
                <AddressContent
                  closeModal={this.closePopup}
                  cart={cart}
                  processAddress={this.processAddress}
                  showEmail={false}
                  showEditButton={false}
                  type="billing"
                  headingText={Drupal.t('billing information')}
                  default_val={editAddressData}
                />
              </React.Suspense>
            </Popup>
          </div>
        </div>
      );
    }

    return (
      <div className="spc-section-billing-address cnc-flow">
        <SectionTitle>{Drupal.t('billing address')}</SectionTitle>
        <div className="spc-billing-address-wrapper">
          <div className="spc-billing-bottom-panel">
            <BillingInfo cart={cart} refreshCart={refreshCart} />
            {shippingAsBilling
            && <div className="spc-billing-help-text">{this.sameBillingAsShippingMessage()}</div>}
          </div>
        </div>
      </div>
    );
  }
}
