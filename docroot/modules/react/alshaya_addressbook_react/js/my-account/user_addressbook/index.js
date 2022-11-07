import React from 'react';
import { getAddressbookInfo, getCustomerDetails } from '../../utilities/addressbook_api_helper';
import Loading from '../../../../js/utilities/loading';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import IndividualAddressItem from '../../components/individual-address-item';
import AddAddressForm from '../add-address-form';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../js/utilities/showRemoveFullScreenLoader';

class UserAddressBook extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      userAddressItems: [],
      defaultValues: [],
      showLoader: true,
      showAddForm: false,
      areaParents: [],
      areaOptions: [],
      addressFields: [],
      customerInfo: null,
      formButtonText: Drupal.t('add address'),
    };
  }

  /**
   * Call the customer API to get the address details.
   */
  componentDidMount() {
    // Get the customer details.
    const customerDetails = getCustomerDetails();

    if (customerDetails instanceof Promise) {
      customerDetails.then((response) => {
        if (hasValue(response)
          && hasValue(response.data)) {
          // Update the state with the user address.
          this.handleCustomerInfoUpdate(response.data);
        }

        // Update the loader flag.
        this.setState({
          showLoader: false,
        });
      });
    }

    // Get the list of options for `area_parents` && `area_options`.
    // Get the address book info.
    const addressInfo = getAddressbookInfo();
    // Show loader until we have the response.
    showFullScreenLoader();
    if (addressInfo instanceof Promise) {
      addressInfo.then((response) => {
        if (response !== false) {
          // Update the state.
          this.setState({
            areaParents: response.area_parents,
            areaOptions: response.area_options,
            areaParentsOptionMapping: response.area_parents_options,
            addressFields: response.address_fields,
          });

          removeFullScreenLoader();
        }
      });
    }
  }

  /**
   * Utility function to process address in required format.
   *
   * @param {array} addressList
   *   An array containing all the address items.
   */
  processAddressData = (addressList) => addressList.sort((a, b) => hasValue(b.default_shipping)
    - hasValue(a.default_shipping));

  /**
   * Get the address item based on the id.
   *
   * @param {string} id
   *   The id of the selected address item.
   *
   * @returns {array}
   *   The customer info array matching the id.
   */
  getAddressItem = (id) => {
    const { userAddressItems } = this.state;
    return userAddressItems.filter((item) => item.id === id);
  }

  /**
   * Toggle the address form display.
   *
   * @param {string} actionType
   *   The type of action we are performing with form.
   * @param {string} id
   *   The id of the selected address item.
   */
  toggleAddressForm = (actionType, id = null) => {
    const { showAddForm } = this.state;
    // Toggle the add form flag.
    if (actionType === 'add') {
      this.setState({
        showAddForm: true,
        defaultValues: [],
        formButtonText: Drupal.t('add address'),
      });
    } else if (actionType === 'edit' && id) {
      this.setState({
        showAddForm: true,
        defaultValues: this.getAddressItem(id),
        formButtonText: Drupal.t('save'),
      });
    } else {
      this.setState({
        showAddForm: !showAddForm,
      });
    }
  };

  /**
   * Update the state with updated customer data.
   *
   * @param {object} customerData
   *   Update customer data object.
   */
  handleCustomerInfoUpdate = (customerData) => {
    let showAddForm = false;
    // Check if the address item length is more than 1 else we will have to
    // display the add address form.
    if (!(customerData.addresses.length > 0)) {
      showAddForm = true;
    }
    // Update the state with the new data.
    this.setState({
      customerInfo: customerData,
      userAddressItems: this.processAddressData(customerData.addresses),
      showAddForm,
    });
  };

  render() {
    // Show loader until we have the address info.
    const {
      showLoader,
      userAddressItems,
      showAddForm,
      defaultValues,
      addressFields,
      areaParents,
      areaOptions,
      areaParentsOptionMapping,
      customerInfo,
      formButtonText,
    } = this.state;

    if (showLoader) {
      return <Loading />;
    }

    return (
      <>
        <a onClick={() => this.toggleAddressForm('add')}>{Drupal.t('Add new Address')}</a>
        <div className="address-book-form-wrapper">
          {showAddForm && (
            <AddAddressForm
              toggleAddressForm={this.toggleAddressForm}
              defaultValues={defaultValues}
              areaParents={areaParents}
              addressFields={addressFields}
              areaParentsOptionMapping={areaParentsOptionMapping}
              formButtonText={formButtonText}
              handleCustomerInfoUpdate={this.handleCustomerInfoUpdate}
            />
          )}
        </div>
        <div className="views-element-container">
          <div className="view-address-book">
            <div className="views-view-grid">
              <div className="view-row">
                {userAddressItems.map((item) => (
                  <div key={item.id} className="user__address--column">
                    <IndividualAddressItem
                      addressItem={item}
                      addressFields={addressFields}
                      areaParents={areaParents}
                      areaOptions={areaOptions}
                      toggleAddressForm={this.toggleAddressForm}
                      customerInfo={customerInfo}
                      handleCustomerInfoUpdate={this.handleCustomerInfoUpdate}
                    />
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
      </>
    );
  }
}

export default UserAddressBook;
