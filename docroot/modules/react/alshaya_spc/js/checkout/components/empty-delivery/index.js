import React from "react";
import Popup from "reactjs-popup";
import Loading from "../../../utilities/loading";
import {
  checkoutAddressProcess,
  getAddressPopupClassName
} from "../../../utilities/checkout_address_process";

let ClickCollect = React.lazy(() => import("../click-collect"));
let AddressContent = React.lazy(() => import("../address-popup-content"));

export default class EmptyDeliveryText extends React.Component {
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

  componentDidMount() {
    this._isMounted = true;
    document.addEventListener("refreshCartOnAddress", this.eventListener, false);

    document.addEventListener(
      "refreshCartOnCnCSelect",
      this.eventListener,
      false
    );

    document.addEventListener('closeAddressListPopup', this.closeModal, false);
  }

  componentWillUnmount() {
    this._isMounted = false;
    document.removeEventListener(
      "refreshCartOnAddress",
      this.eventListener,
      false
    );
    document.removeEventListener(
      "refreshCartOnCnCSelect",
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
            <React.Suspense fallback={<Loading/>}>
              <ClickCollect closeModal={this.closeModal}/>
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
          className={getAddressPopupClassName()}
          open={this.state.open}
          onClose={this.closeModal}
          closeOnDocumentClick={false}
        >
          <React.Suspense fallback={<Loading/>}>
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
