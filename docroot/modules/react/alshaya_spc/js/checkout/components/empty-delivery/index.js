import React from "react";

import Popup from "reactjs-popup";
import { checkoutAddressProcess } from "../../../utilities/checkout_address_process";

let ClickCollect = React.lazy(() => import("../click-collect"));
let AddressContent = React.lazy(() => import("../address-popup-content"));

export default class EmptyDeliveryText extends React.Component {
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

  componentDidMount() {
    document.addEventListener(
      "refreshCartOnAddress",
      e => {
        var data = e.detail.data();
        this.props.refreshCart(data);
        // Close the modal.
        this.closeModal();
      },
      false
    );

    document.addEventListener(
      "refreshCartOnCnCSelect",
      e => {
        var data = e.detail.data();
        this.props.refreshCart(data);
        // Close the modal.
        this.closeModal();
      },
      false
    );
  }

  getAddressPopupClassName = () => {
    return window.drupalSettings.user.uid > 0
      ? "spc-address-list-member"
      : "spc-address-form-guest";
  };

  /**
   * Process the address form data on sumbit.
   */
  processAddress = e => {
    const { cart } = this.props.cart;
    checkoutAddressProcess(e, cart);
  };

  render() {
    const { delivery_type } = this.props.cart;

    if (delivery_type === "cnc") {
      return (
        <div className="spc-empty-delivery-information">
          <div
            onClick={this.openModal}
            className="spc-checkout-empty-delivery-text"
          >
            {Drupal.t("Select your preferred collection store")}
          </div>
          <Popup
            open={this.state.open}
            onClose={this.closeModal}
            closeOnDocumentClick={false}
          >
            <React.Suspense fallback={<div>Loading...</div>}>
              <a className="close" onClick={this.closeModal}>
                &times;
              </a>
              <ClickCollect />
            </React.Suspense>
          </Popup>
        </div>
      );
    }

    return (
      <div className="spc-empty-delivery-information">
        <div
          onClick={this.openModal}
          className="spc-checkout-empty-delivery-text"
        >
          {Drupal.t("Please add your contact details and address.")}
        </div>
        <Popup
          className={this.getAddressPopupClassName()}
          open={this.state.open}
          onClose={this.closeModal}
          closeOnDocumentClick={false}
        >
          <React.Suspense fallback={<div>Loading...</div>}>
            <a className="close" onClick={this.closeModal}>
              &times;
            </a>
            <AddressContent processAddress={this.processAddress} />
          </React.Suspense>
        </Popup>
      </div>
    );
  }
}
