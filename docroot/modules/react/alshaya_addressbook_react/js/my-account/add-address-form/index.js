import React from 'react';
import { makeFullName } from '../../../../alshaya_spc/js/utilities/cart_customer_util';
import { validateInfo } from '../../../../alshaya_spc/js/utilities/checkout_util';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../js/utilities/showRemoveFullScreenLoader';
import { updateCustomerDetails } from '../../utilities/addressbook_api_helper';
import { getDataInMagentoFormat, getProcessedFormData } from '../../utilities/addressbook_util';
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

  handleValidation = (elements) => {
    const errors = [];
    // Loop though all the elements except the optional once.
    Array.from(elements).forEach((item) => {
      if (item.value.length === 0) {
        errors.push(item.name);
      } else {
        document.getElementById(`${item.name}-error`).innerHTML = '';
      }
    });

    if (errors.length > 0) {
      errors.forEach((name) => {
        // Check if element is not empty.
        // `address_line2` is an optional field, so we can skip this.
        // @todo To make it dynamic based on the required flag.
        if (elements[name] && name !== 'address_line2') {
          const label = elements[name].getAttribute('message');
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
      defaultValues,
      handleCustomerInfoUpdate,
      addressFields,
    } = this.props;
    // Perform validation.
    if (!this.handleValidation(elements)) {
      let processedData = getProcessedFormData(elements, defaultValues, addressFields);
      if (hasValue(processedData)) {
        // Show full screen loader.
        showFullScreenLoader();
        const validationRequest = validateInfo(processedData);
        if (validationRequest instanceof Promise) {
          validationRequest.then((result) => {
            if (result.status === 200 && result.data.status) {
              // Before calling the API, convert the address in Magento API
              // required format.
              processedData = getDataInMagentoFormat(processedData, defaultValues, addressFields);
              if (hasValue(processedData)) {
                const customerDetail = updateCustomerDetails(processedData);
                if (customerDetail instanceof Promise) {
                  customerDetail.then((response) => {
                    if (!hasValue(response.errors)
                      && hasValue(response.data)) {
                      handleCustomerInfoUpdate(response.data);

                      // Remove the loader.
                      removeFullScreenLoader();
                    }
                  });
                }
              }
            }
          });
        }
      }
    }
  };

  handleAreaParentChange = (attributeName, value) => {
    // Proceed only if value is not empty.
    if (value) {
      const { areaParentsOptionMapping } = this.state;
      if (hasValue(areaParentsOptionMapping[value])) {
        this.setState({
          areaOptions: areaParentsOptionMapping[value][0],
          defaultAreaParent: value,
        });
      }
    }

    return [];
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
    const { defaultValues, addressFields } = this.props;

    let value = '';
    if (hasValue(addressFields) && hasValue(defaultValues)) {
      const {
        custom_attributes: customAttributes,
        firstname,
        lastname,
        telephone,
        street,
      } = defaultValues[0];

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
        // Update the state if the key is area_parent or administrative_area.
        if (key === 'administrative_area') {
          this.setState({
            defaultAreaParent: value,
          });
        }

        if (key === 'area_parent') {
          this.setState({
            defaultAreaOption: value,
          });
        }
      } else if (key === 'full_name') {
        // Get the full name.
        value = makeFullName(firstname, lastname);
      } else if (key === 'mobile') {
        value = telephone;
      }
    }

    return value;
  };

  handleAreaOptionChange = (attributeName, value) => {
    // Proceed only if value is not empty.
    if (value) {
      this.setState({
        defaultAreaOption: value,
      });
    }

    return [];
  };

  render() {
    const {
      areaParents,
      areaOptions,
      defaultAreaParent,
      defaultAreaOption,
    } = this.state;

    const { toggleAddressForm, addressFields, formButtonText } = this.props;

    const { country_name: countryName } = drupalSettings;

    return (
      <form
        className="profile-form profile-address-book-add-form"
        onSubmit={(e) => this.handleSubmit(e)}
      >
        <div className="field-wrapper">
          <TextField
            name="full_name"
            label={Drupal.t('Full name')}
            defaultValue={this.getDefaultValue('full_name')}
          />

          <div className="address-book-address">
            <TextField
              type="tel"
              name="mobile"
              defaultValue={this.getDefaultValue('mobile')}
              label={Drupal.t('Mobile number')}
            />

            <div className="country-field-wrapper">
              <div className="country-label">{Drupal.t('Country')}</div>
              <div className="country-name">{countryName}</div>
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
                      attributeName={field.key}
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
                      attributeName={field.key}
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
