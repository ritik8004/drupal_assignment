import React from 'react';

import Popup from 'reactjs-popup';
import AddressItem from '../address-item';
import AddressForm from '../address-form';
import {
  getUserAddressList,
  addEditAddressToCustomer,
} from '../../../utilities/address_util';
import {
  showFullScreenLoader,
} from '../../../utilities/checkout_util';

export default class AddressList extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      addressList: [],
      open: false,
    };
  }

  componentDidMount() {
    // If user is logged in, only then get area lists.
    if (window.drupalSettings.user.uid > 0) {
      const addressList = getUserAddressList();
      if (addressList instanceof Promise) {
        addressList.then((list) => {
          this.setState({
            addressList: list,
          });
        });
      }
    }

    document.addEventListener('closeAddressListPopup', this.closeModal, false);
  }

  openModal = () => {
    this.setState({
      open: true,
    });
  }

  closeModal = () => {
    this.setState({
      open: false,
    });
  };

  refreshAddressList = (addressList) => {
    this.setState({
      addressList,
    });
  };

  /**
   * Process add new address.
   */
  processAddress = (e) => {
    // Show loader.
    showFullScreenLoader();
    // If processing method is passed, we use that.
    if (this.props.processAddress !== undefined) {
      this.props.processAddress(e);
    }
    else {
      addEditAddressToCustomer(e);
    }
  };

  render() {
    const { addressList, open } = this.state;
    // If no address list available.
    if (addressList === undefined
      || addressList.length === 0) {
      return (null);
    }

    const { cart, closeModal, headingText, showEditButton, type } = this.props;

    const addressItem = [];
    Object.entries(addressList).forEach(([key, address]) => {
      let fieldToCheck = type === 'billing'
        ? 'billing_address'
        : 'shipping_address';
      const isSelected = (
        cart.cart[fieldToCheck].customer_address_id.toString() === address.address_mdc_id
      );
      addressItem.push(
        <AddressItem
          isSelected={isSelected}
          key={key}
          type={type}
          address={address}
          headingText={headingText}
          processAddress={this.processAddress}
          showEditButton={showEditButton}
          refreshAddressList={this.refreshAddressList}
        />,
      );
    });

    const defaultVal = {
      static: {
        fullname: `${window.drupalSettings.user_name.fname} ${window.drupalSettings.user_name.lname}`,
      },
    };

    return (
      <>
        <header className="spc-change-address">{Drupal.t('change address')}</header>
        <a className="close" onClick={closeModal}>
          &times;
        </a>
        <div className="address-list-content">
          <div className="spc-add-new-address-btn" onClick={this.openModal}>
            {Drupal.t('add new address')}
          </div>
          <Popup open={open} onClose={this.closeModal} closeOnDocumentClick={false}>
            <>
              <AddressForm
                closeModal={this.closeModal}
                showEmail={false}
                show_prefered
                default_val={defaultVal}
                headingText={headingText}
                processAddress={this.processAddress}
              />
            </>
          </Popup>
          <div className="spc-checkout-address-list">{addressItem}</div>
        </div>
      </>
    );
  }
}
