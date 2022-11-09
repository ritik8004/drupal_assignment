import React from 'react';
import { makeFullName } from '../../../../alshaya_spc/js/utilities/cart_customer_util';
import { validateInfo } from '../../../../alshaya_spc/js/utilities/checkout_util';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../js/utilities/showRemoveFullScreenLoader';
import { updateCustomerDetails } from '../../utilities/addressbook_api_helper';
import { getDataInMagentoFormat, getProcessedFormData, getValueFromAddressData } from '../../utilities/addressbook_util';
import SelectList from '../../utilities/selectlist';
import TextField from '../../utilities/textfield';

export default class AddAddressForm extends React.Component {
  constructor(props) {
    super(props);

    const {
      areaParents,
      areaParentsOptionMapping,
    } = this.props;

    this.state = {
      areaParents: this.getProcessedAreaParents(areaParents),
      defaultAreaParent: '',
      defaultAreaOption: '',
      areaParentsOptionMapping: this.getProcessAreaOptions(areaParentsOptionMapping),
      areaOptions: [],
    };
  }

  componentDidMount() {
    const {
      defaultAddressValue,
      addressFields,
    } = this.props;

    // Populate the default value for the select list fields.
    if (hasValue(defaultAddressValue)) {
      const areaParentValue = getValueFromAddressData(defaultAddressValue, 'area_parent', addressFields);
      // Now if area_parent is having any default value then, we will have to
      // trigger the onChange function to populate the value in
      // administrative_area.
      if (hasValue(areaParentValue)) {
        this.handleAreaParentChange('area_parent', areaParentValue);
        const areaOptionValue = getValueFromAddressData(defaultAddressValue, 'administrative_area', addressFields);
        // Update the status now.
        this.setState({
          defaultAreaParent: areaParentValue,
          defaultAreaOption: areaOptionValue,
        });
      }
    }
  }

  /**
   * Get the processed area parents.
   *
   * @param {object} areaParents
   *   The areaParents object.
   */
  getProcessedAreaParents = (areaParents) => {
    const processedAreaParents = [];
    // Process the area parents data.
    Object.keys(areaParents).forEach((key) => {
      const option = {
        value: key,
        label: areaParents[key],
      };
      processedAreaParents.push(option);
    });

    return processedAreaParents;
  }

  /**
   * Get the processed area options.
   *
   * @param {object} areaParentsOptionMapping
   * @returns
   */
  getProcessAreaOptions = (areaParentsOptionMapping) => {
    const processedAreaOptions = [];
    // Process the area option data.
    Object.keys(areaParentsOptionMapping).forEach((key) => {
      const items = areaParentsOptionMapping[key];
      if (!Array.isArray(processedAreaOptions[key])) {
        processedAreaOptions[key] = [];
      }

      processedAreaOptions[key].push(Object.keys(items).map((index) => (
        { value: index, label: items[index] }
      )));
    });

    return processedAreaOptions;
  }

  /**
   * Validates the form submission.
   *
   * @param {object} elements
   *   An object containing the form submission.
   *
   * @returns {boolean}
   *   Return TRUE/FALSE based on the validation.
   */
  handleValidation = (elements) => {
    const errors = [];
    // Loop though all the elements except the optional once.
    Array.from(elements).forEach((item) => {
      // We will have to make sure to validate the value of area_parent &
      // administrative_area as these fields are required. And with react-select
      // we cannot pass required flag.
      if (hasValue(item.name)
        && (item.required
        || item.name === 'area_parent'
        || item.name === 'administrative_area')
        && item.value.length === 0) {
        // Push the items in the error array to display the error messages based
        // on item name.
        errors.push(item.name);
      } else if (hasValue(item.name)) {
        document.getElementById(`${item.name}-error`).innerHTML = '';
      }
    });

    if (errors.length > 0) {
      errors.forEach((name) => {
        // Check if element is not empty.
        if (elements[name]) {
          let label = elements[name].getAttribute('message');
          // For area_parent & administrative_area, get the message from label.
          if (name === 'area_parent' || name === 'administrative_area') {
            label = document.getElementById(`${name}-error`).parentNode.getElementsByTagName('label')[0].innerHTML;
          }
          if (hasValue(label)) {
            document.getElementById(`${name}-error`).innerHTML = Drupal.t('Please enter your @title.', { '@title': label });
          }
        }
      });

      return false;
    }

    return true;
  };

  // Submit handler for form.
  handleSubmit = (e) => {
    e.preventDefault();
    const { elements } = e.target;
    const {
      customerInfo,
      handleCustomerInfoUpdate,
      addressFields,
      areaOptions,
      defaultAddressValue,
    } = this.props;

    // Perform frontend validation once.
    if (this.handleValidation(elements)) {
      let processedData = getProcessedFormData(elements, addressFields, areaOptions);
      if (Object.keys(processedData).length > 0) {
        // Show full screen loader.
        showFullScreenLoader();
        const validationRequest = validateInfo(processedData);
        if (validationRequest instanceof Promise) {
          validationRequest.then((result) => {
            if (result.status === 200 && result.data.status) {
              // Before calling the API, convert the address in Magento API
              // required format.
              let addressItemId = null;
              if (hasValue(defaultAddressValue)) {
                addressItemId = defaultAddressValue.id;
              }
              processedData = getDataInMagentoFormat(processedData, customerInfo, addressItemId);
              if (hasValue(processedData)) {
                const customerDetail = updateCustomerDetails(processedData);
                if (customerDetail instanceof Promise) {
                  customerDetail.then((response) => {
                    if (!hasValue(response.errors)
                      && hasValue(response.data)) {
                      handleCustomerInfoUpdate(response.data);
                      let message = '';
                      // There are two type operation we are doing right now,
                      // update or add. And we can identify based on the default
                      // value.
                      if (hasValue(addressItemId)) {
                        message = Drupal.t('Address is updated successfully.');
                      } else {
                        message = Drupal.t('Address is added successfully.');
                      }
                      // Show the confirmation message.
                      Drupal.alshayaAddressBookReactShowGlobalMessage(
                        message,
                        'status',
                      );
                      // Remove the loader.
                      removeFullScreenLoader();
                    } else {
                      // Show the error message.
                      Drupal.alshayaAddressBookReactShowGlobalMessage(
                        drupalSettings.globalErrorMessage,
                        'error',
                      );
                    }
                  });
                }
              }
            } else if (result.status === 200 && !result.data.status) {
              // @todo Handle the BE validation here.
            } else if (result.status !== 200) {
              // Show the error message.
              Drupal.alshayaAddressBookReactShowGlobalMessage(
                drupalSettings.globalErrorMessage,
                'error',
              );
            }
          });
        }
      }
    }
  };

  /**
   * Handles the on change operation of administrative_area.
   *
   * @param {string} attributeName
   *   The attribute name.
   * @param {string} value
   *   The selected value from the selectlist.
   */
  handleAreaOptionChange = (attributeName, value) => {
    // Proceed only if value is not empty.
    if (value) {
      this.setState({
        defaultAreaOption: value,
      });
      // Remove the error message if any.
      document.getElementById(`${attributeName}-error`).innerHTML = '';
    }
  };

  /**
   * Handles the on change operation of area_parent.
   *
   * @param {string} attributeName
   *   The attribute name.
   * @param {string} value
   *   The selected value from the selectlist.
   */
  handleAreaParentChange = (attributeName, value) => {
    // Proceed only if value is not empty.
    if (value) {
      const { areaParentsOptionMapping } = this.state;
      if (hasValue(areaParentsOptionMapping[value])) {
        this.setState({
          areaOptions: areaParentsOptionMapping[value][0],
          defaultAreaParent: value,
        });
        // Remove the error message if any.
        document.getElementById(`${attributeName}-error`).innerHTML = '';
      }
    }
  };

  /**
   * Get the default value of the fields.
   *
   * @param {string} key
   *   The key of the field.
   *
   * @return {string}
   *   The default value if exists.
   */
  getDefaultValue = (key) => {
    const { defaultAddressValue, addressFields } = this.props;

    let value = '';
    if (hasValue(addressFields) && hasValue(defaultAddressValue)) {
      const {
        custom_attributes: customAttributes,
        firstname,
        lastname,
        telephone,
        street,
      } = defaultAddressValue;

      // Check if the key is of address field.
      if (hasValue(addressFields[key])) {
        // Add the exception check for `street` as it's not the part of custom
        // attributes.
        const addresssFieldKey = addressFields[key].key;
        if (addresssFieldKey !== 'street') {
          value = customAttributes.filter((item) => item.name === addresssFieldKey);
        } else {
          value = street;
        }
        // Extract the value.
        if (value.length > 0) {
          if (addresssFieldKey === 'street') {
            [value] = street;
          } else {
            value = value[0].value;
          }
        }
      } else if (key === 'fullname') {
        // Get the full name.
        value = makeFullName(firstname, lastname);
      } else if (key === 'mobile') {
        value = telephone;
      }
    }

    return value;
  };

  render() {
    const {
      areaParents,
      areaOptions,
      defaultAreaParent,
      defaultAreaOption,
    } = this.state;

    const { toggleAddressForm, addressFields, formButtonText } = this.props;

    const { country } = drupalSettings.gtm;

    return (
      <form
        className="profile-form profile-address-book-add-form"
        onSubmit={(e) => this.handleSubmit(e)}
      >
        <div className="field-wrapper">
          <TextField
            name="fullname"
            label={Drupal.t('Full name')}
            required
            maxLength="128"
            defaultValue={this.getDefaultValue('fullname')}
          />

          <div className="address-book-address">
            <TextField
              type="tel"
              name="mobile"
              required
              defaultValue={this.getDefaultValue('mobile')}
              label={Drupal.t('Mobile number')}
            />

            <div className="country-field-wrapper">
              <div className="country-label">{Drupal.t('Country')}</div>
              <div className="country-name">{country}</div>
            </div>

            {/* Show all the address fields */}
            <div className="address-book-fields">
              {Object.keys(addressFields).map((index) => {
                const field = addressFields[index];
                let element = null;

                if (index === 'area_parent') {
                  element = (
                    <SelectList
                      key={index}
                      label={field.label}
                      options={areaParents}
                      attributeName={index}
                      defaultValue={defaultAreaParent}
                      onChange={this.handleAreaParentChange}
                    />
                  );
                } else if (index === 'administrative_area') {
                  element = (
                    <SelectList
                      key={field.key}
                      label={field.label}
                      options={areaOptions}
                      attributeName={index}
                      defaultValue={defaultAreaOption}
                      onChange={this.handleAreaOptionChange}
                    />
                  );
                } else {
                  element = (
                    <TextField
                      key={field.key}
                      name={index}
                      label={field.label}
                      required={field.required}
                      maxLength={field.maxLength}
                      defaultValue={this.getDefaultValue(index)}
                    />
                  );
                }

                return element;
              })}
            </div>
          </div>
        </div>
        <div className="form-actions">
          <button
            id="save-address"
            className="form-submit"
            type="submit"
          >
            {formButtonText}
          </button>

          <a onClick={() => toggleAddressForm('cancel')} className="cancel-button button">{Drupal.t('cancel')}</a>
        </div>
      </form>
    );
  }
}
