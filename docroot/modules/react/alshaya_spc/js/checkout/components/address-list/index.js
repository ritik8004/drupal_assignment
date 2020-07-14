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
import getStringMessage from '../../../utilities/strings';
import WithModal from '../with-modal';
import dispatchCustomEvent from '../../../utilities/events';
import Loading from '../../../utilities/loading';

export default class AddressList extends React.Component {
  isComponentMounted = false;

  constructor(props) {
    super(props);
    this.state = {
      addressList: [],
    };
  }

  componentDidMount() {
    this.isComponentMounted = true;
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

    document.addEventListener('closeAddressListPopup', this.closeModal);
  }

  componentWillUnmount() {
    this.isComponentMounted = false;
    document.removeEventListener('closeAddressListPopup', this.closeModal);
  }

  closeModal = () => {
    if (this.isComponentMounted) {
      dispatchCustomEvent('closeModal', 'addNewAddress');
    }
  }

  refreshAddressList = (addressList) => {
    this.setState({
      addressList,
    });
  };

  /**
   * Process add new address.
   */
  processAddress = (e) => {
    const { type, processAddress } = this.props;
    // Show loader.
    showFullScreenLoader();
    // If processing method is passed, we use that.
    if (type === 'billing') {
      processAddress(e);
    } else {
      addEditAddressToCustomer(e);
    }
  };

  render() {
    const { addressList } = this.state;
    // If no address list available.
    if (addressList === undefined || addressList.length === 0) {
      return <Loading />;
    }

    const {
      cart, closeModal, headingText, showEditButton, type, formContext,
    } = this.props;

    const addressItem = [];
    Object.entries(addressList).forEach(([key, address]) => {
      const addressData = (type === 'billing')
        ? cart.cart.billing_address
        : cart.cart.shipping.address;
      let isSelected = false;
      if (addressData && addressData.city !== 'NONE'
        && (cart.cart.shipping.type === 'home_delivery' || type === 'billing')
        && addressData.customer_address_id !== undefined
        && addressData.customer_address_id.toString() === address.address_mdc_id) {
        isSelected = true;
      }
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
        telephone: drupalSettings.user_name.mobile,
      },
    };

    return (
      <>
        <header className="spc-change-address">{getStringMessage('change_address')}</header>
        <a className="close" onClick={() => closeModal()}>
          &times;
        </a>
        <div className="address-list-content">
          <WithModal modalStatusKey="addNewAddress">
            {({ triggerOpenModal, triggerCloseModal, isModalOpen }) => (
              <>
                <div className="spc-add-new-address-btn" onClick={() => triggerOpenModal(2)}>
                  {getStringMessage('add_new_address')}
                </div>
                <Popup open={isModalOpen} closeOnDocumentClick={false} closeOnEscape={false}>
                  <AddressForm
                    closeModal={triggerCloseModal}
                    showEmail={false}
                    show_prefered
                    default_val={defaultVal}
                    headingText={headingText}
                    processAddress={this.processAddress}
                    formContext={formContext}
                  />
                </Popup>
              </>
            )}
          </WithModal>
          <div className="spc-checkout-address-list">{addressItem}</div>
        </div>
      </>
    );
  }
}
