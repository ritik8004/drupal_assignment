import React from "react";

import Popup from "reactjs-popup";
import ShippingMethods from "../shipping-methods";
import Loading from "../../../utilities/loading";
import {
  checkoutAddressProcess,
  getAddressPopupClassName
} from "../../../utilities/checkout_address_process";
import {
  showFullScreenLoader
} from "../../../utilities/checkout_util";

let AddressContent = React.lazy(() => import("../address-popup-content"));

export default class HomeDeliveryInfo extends React.Component {
  _isMounted = false;
  constructor(props) {
    super(props);
    this.state = { open: false };
  }

  openModal = () => {
    this.setState({ open: true });
  };

  closeModal = () => {
    this.setState({ open: false });
  };

  processAddress = e => {
    const { cart } = this.props.cart;
    // Show the loader.
    showFullScreenLoader();
    checkoutAddressProcess(e, cart);
  };

  componentDidMount() {
    this._isMounted = true;
    document.addEventListener(
      'refreshCartOnAddress',
      this.eventListener,
      false
    );
  }

  componentWillUnmount() {
    this._isMounted = false;
    document.removeEventListener(
      "refreshCartOnAddress",
      this.eventListener,
      false
    );
  }

  eventListener = e => {
    var data = e.detail.data();
    this.props.refreshCart(data);
    if (this._isMounted) {
      this.closeModal();
    }
  };

  /**
   * Format address for edit address.
   */
  formatAddressData = (address) => {
    let formatted_address = {
      'static': {
        'fullname': address.firstname + ' ' + address.lastname,
        'email': address.email,
        'telephone': address.telephone
      }
    };

    Object.entries(window.drupalSettings.address_fields).forEach(
      ([key, field]) => {
        formatted_address[field['key']] = address[field['key']];
      }
    );

    return formatted_address;
  }

  render() {
    const address = this.props.cart.cart.shipping_address;
    let addressData = [];
    Object.entries(window.drupalSettings.address_fields).forEach(([key, val]) => {
      let fillVal = address[val.key];
      // Handling for area field.
      if (key === 'administrative_area') {
        const area = document.querySelectorAll('[data-id="' + fillVal + '"]');
        // If area available only then use.
        if (area.length > 0) {
          fillVal = area[0].getAttribute('data-label')
        }
      }
      addressData.push(<span key={key}>{fillVal}, </span>)
    })

    return (
      <div className="delivery-information-preview">
        <div className="spc-delivery-customer-info">
          <div className="delivery-name">
            {address.firstname} {address.lastname}
          </div>
          <div className="delivery-address">
            {addressData}
          </div>
          <div className="spc-address-form-edit-link" onClick={this.openModal}>
            {Drupal.t("Change")}
          </div>
        </div>
        <Popup
          open={this.state.open}
          onClose={this.closeModal}
          closeOnDocumentClick={false}
          className={getAddressPopupClassName()}
        >
          <React.Suspense fallback={<Loading/>}>
            <a className="close" onClick={this.closeModal}>
              &times;
            </a>
            <AddressContent
              cart={this.props.cart}
              processAddress={this.processAddress}
              showEmail={window.drupalSettings.user.uid === 0}
              default_val = {
                window.drupalSettings.user.uid === 0
                  ? this.formatAddressData(address)
                  : null
              }
            />
          </React.Suspense>
        </Popup>
        <div className="spc-delivery-shipping-methods">
          <ShippingMethods
            cart={this.props.cart}
            refreshCart={this.props.refreshCart}
          />
        </div>
      </div>
    );
  }
}
