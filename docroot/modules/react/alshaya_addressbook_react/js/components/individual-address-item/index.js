import React from 'react';
import Popup from 'reactjs-popup';
import { makeFullName } from '../../../../alshaya_spc/js/utilities/cart_customer_util';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../js/utilities/showRemoveFullScreenLoader';
import { updateCustomerDetails } from '../../utilities/addressbook_api_helper';
import { getDeliveryInfo } from '../../utilities/addressbook_util';

class IndividualAddressItem extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      // This flag we are using to show the delete wraning message.
      deleteWarningModalOpen: false,
    };
  }

  /**
   * Change the default address.
   */
  handleDefaultAddressChange = () => {
    const { customerInfo, handleCustomerInfoUpdate, addressItem } = this.props;
    // Traverse through the address array and change the `default_shipping`
    // flag.
    const addressList = [];
    customerInfo.addresses.forEach((item) => {
      const individualItem = { ...item };
      individualItem.default_shipping = false;
      individualItem.default_billing = false;

      if (individualItem.id === addressItem.id) {
        individualItem.default_shipping = true;
        individualItem.default_billing = true;
      }
      addressList.push(individualItem);
    });

    // Update the address list.
    customerInfo.addresses = addressList;

    // Call the API to update the customer detail.
    const updateCustomerDetail = updateCustomerDetails(customerInfo);
    // Show the full screen loader.
    showFullScreenLoader();
    if (updateCustomerDetail instanceof Promise) {
      updateCustomerDetail.then((response) => {
        if (!hasValue(response.errors)
          && hasValue(response.data)) {
          // Update the state with the new data.
          handleCustomerInfoUpdate(response.data);
        }

        // Remove the loader.
        removeFullScreenLoader();
      });
    }
  }

  /**
   * Show / Hide the modal based on the status.
   *
   * @param {boolean} status
   *   The status of the modal.
   */
  triggerDeleteModal = (status) => {
    this.setState({
      deleteWarningModalOpen: status,
    });
  }

  handleAddressDelete = () => {
    const { addressItem, customerInfo, handleCustomerInfoUpdate } = this.props;
    // Check and remove the address from the list.
    const addressList = [];
    customerInfo.addresses.forEach((item) => {
      if (item.id !== addressItem.id) {
        addressList.push(item);
      }
    });

    // Update the customer address array with the updated one.
    customerInfo.addresses = addressList;

    // Call the API to update the customer detail.
    const updateCustomerDetail = updateCustomerDetails(customerInfo);
    // Show the full screen loader.
    showFullScreenLoader();
    if (updateCustomerDetail instanceof Promise) {
      updateCustomerDetail.then((response) => {
        if (!hasValue(response.errors)
          && hasValue(response.data)) {
          // Update the state with the new data.
          handleCustomerInfoUpdate(response.data);
        }

        // Remove the loader.
        removeFullScreenLoader();
      });
    }
  }

  render() {
    const {
      addressItem,
      toggleAddressForm,
      addressFields,
      areaParents,
      areaOptions,
    } = this.props;

    // Return from here if the address item is empty.
    if (!hasValue(addressItem)) {
      return '';
    }

    const {
      firstname,
      lastname,
      telephone,
      default_shipping: defaultShipping,
      id,
    } = addressItem;

    const { deleteWarningModalOpen } = this.state;

    const deliveryInfo = getDeliveryInfo(addressItem, addressFields);
    return (
      <>
        <div className={`address ${defaultShipping ? 'default' : ''}`}>
          <div className="address--data">
            <div className="address--userinfo">
              {firstname && lastname && (
                <div>
                  <div className="address--label">{Drupal.t('Delivery to')}</div>
                  <div className="address--content">
                    {makeFullName(firstname, lastname)}
                  </div>
                </div>
              )}
              {telephone && (
                <div className="address--contact">
                  <div className="address--label">{Drupal.t('Contact number')}</div>
                  <div className="address--content">{telephone}</div>
                </div>
              )}
            </div>
            <div className="address--delivery">
              <div className="address--label">{Drupal.t('Delivery address')}</div>
              {Object.keys(deliveryInfo).map((key) => {
                let addressElement = '';
                if (key === 'administrative_area') {
                  addressElement = (
                    <div key={key} className="address--content">{areaOptions[deliveryInfo[key]]}</div>
                  );
                } else if (key === 'area_parent') {
                  addressElement = (
                    <div key={key} className="address--content">{areaParents[deliveryInfo[key]]}</div>
                  );
                } else {
                  addressElement = (
                    <div key={key} className="address--content">{deliveryInfo[key]}</div>
                  );
                }
                return addressElement;
              })}
            </div>
          </div>
          <div className="address--options">
            {defaultShipping && (
              <div className="address--primary address--controls">
                <span>{Drupal.t('Primary address')}</span>
              </div>
            )}
            {!defaultShipping && (
              <div className="address--primary address--controls">
                <a onClick={this.handleDefaultAddressChange}>{Drupal.t('Primary address')}</a>
              </div>
            )}
            <div className="address--edit address--controls">
              <a onClick={() => toggleAddressForm('edit', id)}>{Drupal.t('Edit')}</a>
            </div>
            {!defaultShipping && (
              <div className="address--delete address--controls">
                <a onClick={() => this.triggerDeleteModal(true)}>{Drupal.t('Delete')}</a>
              </div>
            )}
          </div>
        </div>

        <Popup
          className="address-delete-warning-modal"
          open={deleteWarningModalOpen}
          closeOnEscape={false}
          closeOnDocumentClick={false}
        >
          <div className="modal">
            <button type="button" className="close" onClick={() => this.triggerDeleteModal(false)}>
              &times;
            </button>
            <div className="header">{Drupal.t('Delete address')}</div>
            <div className="content">
              {Drupal.t('You have selected to delete this address, are you sure?')}
            </div>
            <div className="actions">
              <button type="button" onClick={this.handleAddressDelete}>{Drupal.t('yes, delete this address')}</button>
              <button type="button" className="button" onClick={() => this.triggerDeleteModal(false)}>{Drupal.t('No, take me back')}</button>
            </div>
          </div>
        </Popup>
      </>
    );
  }
}

export default IndividualAddressItem;
