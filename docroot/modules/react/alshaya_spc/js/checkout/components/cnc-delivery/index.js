import React from "react";
import Popup from "reactjs-popup";
import { checkoutAddressProcess } from "../../../utilities/checkout_address_process";
import ClickCollect from "../click-collect";

class ClicknCollectDeiveryInfo extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      open: false,
      showSelectedStore: false
    };
  }

  openModal = showSelectedStore => {
    this.setState({
      open: true,
      showSelectedStore: showSelectedStore || false
    });
  };

  closeModal = () => {
    this.setState({ open: false });
  };

  processAddress = e => {
    const { cart } = this.props.cart;
    checkoutAddressProcess(e, cart);
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
  }

  render() {
    const {
      cart: {
        store_info: { name, cart_address: address },
        shipping_address
      }
    } = this.props.cart;

    return (
      <div className="delivery-information-preview">
        <div className="spc-delivery-store-info">
          <div className="store-name">{name}</div>
          <div className="store-address">
            {address.extension.address_block_segment},{" "}
            {address.extension.address_building_segment},{" "}
            {address.extension.address_apartment_segment}, {address.street}
          </div>
          <div
            className="spc-change-address-link"
            onClick={() => this.openModal(false)}
          >
            {Drupal.t("Change")}
          </div>
        </div>
        <div className="spc-delivery-contact-info">
          <div className="contact-info-label">{Drupal.t("Collection by")}</div>
          <div className="contact-name">
            {shipping_address.firstname} {shipping_address.lastname}
          </div>
          <div className="contact-telephone">{shipping_address.telephone}</div>
          <div
            className="spc-change-address-link"
            onClick={() => this.openModal(true)}
          >
            {Drupal.t("Change")}
          </div>
        </div>
        <Popup
          open={this.state.open}
          onClose={this.closeModal}
          closeOnDocumentClick={false}
        >
          <React.Fragment>
            <a className="close" onClick={this.closeModal}>
              &times;
            </a>
            <ClickCollect openSelectedStore={this.state.showSelectedStore} />
          </React.Fragment>
        </Popup>
      </div>
    );
  }
}

export default ClicknCollectDeiveryInfo;
