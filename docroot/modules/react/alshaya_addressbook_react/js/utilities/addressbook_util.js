import { extractFirstAndLastName } from '../../../alshaya_spc/js/utilities/cart_customer_util';
import { hasValue } from '../../../js/utilities/conditionsUtility';

/**
 * Utility function to process the submitted form data.
 *
 * @param {object} elements
 *   The form input object.
 * @param {object} address_fields
 *   The address field object.
 * @param {object} areaOptions
 *   Mapping of area key and area name.
 */
const getProcessedFormData = (elements, addressFields, areaOptions) => {
  // Prepare the form data for validation.
  const processedFormData = {};
  Array.from(elements).forEach((item) => {
    if (hasValue(item.name)) {
      // Get firstname and lastname from the full name.
      if (item.name === 'fullname') {
        processedFormData[item.name] = extractFirstAndLastName(item.value);
      } else {
        processedFormData[item.name] = item.value;
      }
    }
  });

  const customAttributes = [];
  // Traverse through the address fields and update the values.
  Object.keys(addressFields).forEach((key) => {
    const addressKey = addressFields[key].key;
    if (addressKey === 'street') {
      // Street is not a part of custom_attributes and the expected value is
      // array.
      processedFormData[addressKey] = [processedFormData[key]];
    } else {
      customAttributes.push({
        attribute_code: addressKey,
        value: processedFormData[key],
      });
    }
    // City is Magento core field but we don't use it at all.
    // But this is required by Cybersource so we need proper value.
    // For now, we copy value of Area to City.
    if (addressKey === 'area') {
      processedFormData.city = areaOptions[processedFormData[key]];
    }
  });

  if (!hasValue(processedFormData.address)) {
    processedFormData.address = {};
  }

  // Update the custom attributes in the customer data.
  processedFormData.address.custom_attributes = customAttributes;

  return processedFormData;
};

/**
 * Utility function to convert the drupal data in Magento API format.
 *
 * @param {array} processedData
 *   The form data in drupal format.
 * @param {object} customerInfo
 *   An object containing the customer information.
 * @param {integer} addressItemId
 *   Id of the address item which is getting updated.
 *
 * @return {array}
 *   The form data in Magento API format.
 */
const getDataInMagentoFormat = (processedData, customerInfo, addressItemId) => {
  const { userDetails } = drupalSettings;
  const { regional } = drupalSettings.alshaya_geolocation;
  // Copy the custom info in a separate variable to alter the changes.
  const customerData = { ...customerInfo };

  if (hasValue(userDetails)) {
    // Extract the firstname and lastname.
    const name = extractFirstAndLastName(userDetails.userName);
    customerData.firstname = name.firstname;
    customerData.lastname = name.lastname;
    customerData.email = userDetails.userEmailID;

    // Now prepare the address array.
    let address = {};
    if (hasValue(addressItemId)) {
      [address] = customerData.addresses.filter((item) => item.id === addressItemId);
      customerData.addresses = customerData.addresses.filter(
        (item) => !(item.id === addressItemId),
      );
    }
    const { firstname, lastname } = processedData.fullname;
    if (!hasValue(customerData.addresses)) {
      customerData.addresses = [];
    }
    address.firstname = firstname;
    address.lastname = lastname;
    address.telephone = processedData.mobile;
    address.country_id = regional;
    address.city = processedData.city;

    // Any new address will be considered as the default address.
    address.default_billing = true;
    address.default_shipping = true;

    // We already have the prepared custom_attribute from the
    // getProcessedFormData function.
    if (hasValue(processedData.address)
      && hasValue(processedData.address.custom_attributes)) {
      address.custom_attributes = processedData.address.custom_attributes;
    }

    // Check if the street value is also available in the processedData.
    if (hasValue(processedData.street)) {
      address.street = processedData.street;
    }

    // Update the address in the customer data.
    customerData.addresses.push(address);
  }

  return customerData;
};

/**
 * Utility function to get the value of all required address items.
 *
 * @param {object} addressItem
 *   The address value object.
 * @param {object} addressFields
 *   The address field mapping object.
 *
 * @return {array}
 *   An array containing the value of address items.
 */
const getDeliveryInfo = (addressItem, addressFields) => {
  const valueOfAddressItems = {};
  const {
    street,
    custom_attributes: customAttributes,
  } = addressItem;
  // Get the country name.
  if (hasValue(drupalSettings.gtm)) {
    valueOfAddressItems.country = drupalSettings.gtm.country;
  }
  // Traverse through the address fields and get the values.
  Object.keys(addressFields).forEach((key) => {
    const addressKey = addressFields[key].key;
    // Except street, get all the values from `custom_attributes`.
    if (addressKey === 'street') {
      valueOfAddressItems[key] = street.length > 0 ? street[0] : '';
    } else {
      const filteredItem = customAttributes.filter((item) => item.name === addressKey);
      if (hasValue(filteredItem)) {
        valueOfAddressItems[key] = filteredItem[0].value;
      }
    }
  });

  return valueOfAddressItems;
};

/**
 * Utility function to get the value of provided key.
 *
 * @param {object} defaultAddressValue
 *   The default values of the form fields.
 * @param {string} key
 *   Address field key
 * @param {object} addressFieldMapping
 *   An object containing the mapping between Drupal & Magento fields.
 */
const getValueFromAddressData = (defaultAddressValue, key, addressFieldMapping) => {
  if (hasValue(defaultAddressValue) && hasValue(addressFieldMapping[key])) {
    const filteredItem = defaultAddressValue.custom_attributes.filter(
      (item) => item.name === addressFieldMapping[key].key,
    );
    // Return the value if the result is not empty.
    if (hasValue(filteredItem)) {
      return filteredItem[0].value;
    }
  }

  return '';
};

export {
  getProcessedFormData,
  getDeliveryInfo,
  getDataInMagentoFormat,
  getValueFromAddressData,
};
