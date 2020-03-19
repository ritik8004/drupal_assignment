import React from 'react';

import Popup from 'reactjs-popup';
import Loading from '../../../utilities/loading';
import BillingInfo from '../billing-info';
import SectionTitle from '../../../utilities/section-title';
import {
  processBillingUpdateFromForm,
  getAddressPopupClassName
} from '../../../utilities/checkout_address_process';

const AddressContent = React.lazy(() => import('../address-popup-content'));

export default class CnCBillingAddress extends React.Component {
  isComponentMounted = false;

  constructor(props) {
    super(props);
    this.state = {
      open: false,
    };
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

  componentDidMount() {
    this.isComponentMounted = true;
    document.addEventListener('onBillingAddressUpdate', this.processBillingUpdate, false);
  }

  componentWillUnmount() {
    this.isComponentMounted = false;
    document.removeEventListener('onBillingAddressUpdate', this.processBillingUpdate, false);
  }

  /**
   * Event handler for billing update.
   */
  processBillingUpdate = (e) => {
    if (this.isComponentMounted) {
      const data = e.detail.data();
      const { refreshCart } = this.props;
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

  render() {
    const { cart, refreshCart } = this.props;
    const { open } = this.state;

    // If carrier info not set, means shipping info not set.
    // So we don't need to show bulling.
    if (cart.cart.carrier_info === undefined ||
      cart.cart.carrier_info === null) {
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
    let editAddressData = {
      static: {
        fullname: `${shippingAddress.firstname} ${shippingAddress.lastname}`,
        telephone: shippingAddress.telephone
      }
    };

    // If user has not added billing address.
    if (!billingAddressAddedByUser) {
      return (
        <div className="spc-section-billing-address cnc-flow">
          <SectionTitle>{Drupal.t('Billing address')}</SectionTitle>
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
                  cart={this.props.cart}
                  processAddress={this.processAddress}
                  showEmail={false}
                  showEditButton={false}
                  type={'billing'}
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
            <BillingInfo cart={cart} refreshCart={refreshCart}/>
          </div>
        </div>
      </div>
    );
  }
}
