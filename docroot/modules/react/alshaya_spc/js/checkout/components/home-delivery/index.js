import React from 'react';

import Popup from 'reactjs-popup';
import ShippingMethods from '../shipping-methods';
import Loading from '../../../utilities/loading';
import {
  checkoutAddressProcess,
  getAddressPopupClassName,
  formatAddressDataForEditForm,
} from '../../../utilities/checkout_address_process';
import {
  showFullScreenLoader,
} from '../../../utilities/checkout_util';
import {
  gerAreaLabelById,
} from '../../../utilities/address_util';

const AddressContent = React.lazy(() => import('../address-popup-content'));

export default class HomeDeliveryInfo extends React.Component {
  _isMounted = false;

  constructor(props) {
    super(props);
    this.state = { open: false };
  }

  componentDidMount() {
    this._isMounted = true;
    document.addEventListener(
      'refreshCartOnAddress',
      this.eventListener,
      false,
    );
  }

  componentWillUnmount() {
    this._isMounted = false;
    document.removeEventListener(
      'refreshCartOnAddress',
      this.eventListener,
      false,
    );
  }

  openModal = () => {
    this.setState({ open: true });
  };

  closeModal = () => {
    this.setState({ open: false });
  };

  processAddress = (e) => {
    // Show the loader.
    showFullScreenLoader();
    checkoutAddressProcess(e);
  };

  eventListener = (e) => {
    const data = e.detail.data();
    const { refreshCart } = this.props;
    refreshCart(data);
    if (this._isMounted) {
      this.closeModal();
    }
  };

  /**
   * Format address for edit address.
   */
  formatAddressData = (address) => formatAddressDataForEditForm(address)

  render() {
    const {
      cart: { cart: { shipping_address: address } },
      cart: cartVal,
      refreshCart,
    } = this.props;
    const { open } = this.state;
    let addressData = [];
    Object.entries(window.drupalSettings.address_fields).forEach(([key, val]) => {
      if (address[val.key] !== undefined) {
        let fillVal = address[val.key];
        // Handling for area field.
        if (key === 'administrative_area') {
          fillVal = gerAreaLabelById(false, fillVal);
        } else if (key === 'area_parent') {
          fillVal = gerAreaLabelById(true, fillVal);
        }
        addressData.push(fillVal);
      }
    });

    return (
      <div className="delivery-information-preview">
        <div className="spc-delivery-customer-info">
          <div className="delivery-name">
            {address.firstname}
            {' '}
            {address.lastname}
          </div>
          <div className="delivery-address">
            {addressData.join(', ')}
          </div>
          <div className="spc-address-form-edit-link" onClick={this.openModal}>
            {Drupal.t('Change')}
          </div>
        </div>
        <Popup
          open={open}
          onClose={this.closeModal}
          closeOnDocumentClick={false}
          className={getAddressPopupClassName()}
        >
          <React.Suspense fallback={<Loading />}>
            <AddressContent
              cart={cartVal}
              closeModal={this.closeModal}
              processAddress={this.processAddress}
              showEmail={window.drupalSettings.user.uid === 0}
              default_val={
                window.drupalSettings.user.uid === 0
                  ? this.formatAddressData(address)
                  : null
              }
            />
          </React.Suspense>
        </Popup>
        <div className="spc-delivery-shipping-methods">
          <ShippingMethods
            cart={cartVal}
            refreshCart={refreshCart}
          />
        </div>
      </div>
    );
  }
}
