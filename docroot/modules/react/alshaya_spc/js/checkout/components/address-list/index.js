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
import { getDeliveryAreaStorage } from '../../../utilities/delivery_area_util';

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
    if (drupalSettings.user.uid > 0) {
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
      cart, closeModal, headingText, showEditButton, type, formContext, areaUpdated,
    } = this.props;

    const addressItem = [];
    // Get Selected Area.
    const areaSelected = getDeliveryAreaStorage();
    let isSelected = false;
    Object.entries(addressList).forEach(([key, address]) => {
      const addressData = (type === 'billing')
        ? cart.cart.billing_address
        : cart.cart.shipping.address;
      if (addressData && addressData.city !== 'NONE'
        && (cart.cart.shipping.type === 'home_delivery' || type === 'billing')
        && addressData.customer_address_id !== undefined
        && addressData.customer_address_id.toString() === address.address_mdc_id) {
        isSelected = true;
      }
      // Mark address not selected if area is updated
      if (areaUpdated
        && areaSelected.area !== 'undefined'
        && (areaSelected.area !== address.administrative_area)) {
        isSelected = false;
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
          areaUpdated={areaUpdated}
        />,
      );
    });

    // Show New Address form on if Area is updated,
    // And updated area is not available in Address List.
    let showNewAddressForm = false;
    if (areaUpdated && !isSelected) {
      showNewAddressForm = true;
    }

    // Get Default Value form Form.
    const defaultVal = {
      static: {
        fullname: `${window.drupalSettings.user_name.fname} ${window.drupalSettings.user_name.lname}`,
        telephone: drupalSettings.user_name.mobile,
      },
      address_city_segment: areaSelected !== null ? areaSelected.value.address_city_segment : '',
      area: areaSelected !== null ? areaSelected.value.area : '',
    };

    return (
      <>
        <header className="spc-change-address">{getStringMessage('change_address')}</header>
        <a className="close" onClick={() => closeModal()}>
          &times;
        </a>
        <div className="address-list-content">
          <WithModal modalStatusKey="addNewAddress">
            {({ triggerOpenModal, triggerCloseModal }) => (
              <>
                <div className="spc-add-new-address-btn" onClick={() => triggerOpenModal(2)}>
                  {getStringMessage('add_new_address')}
                </div>
                <Popup
                  className="spc-address-list-new-address"
                  open={showNewAddressForm}
                  closeOnDocumentClick={false}
                  closeOnEscape={false}
                >
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
