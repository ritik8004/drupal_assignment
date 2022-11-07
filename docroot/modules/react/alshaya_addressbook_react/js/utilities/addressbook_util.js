import { extractFirstAndLastName } from '../../../alshaya_spc/js/utilities/cart_customer_util';
import { hasValue } from '../../../js/utilities/conditionsUtility';

/**
 * Utility function to process the submitted form data.
 *
 * @param {*} elements
 */
const getProcessedFormData = (elements, defaultValues, addressFields) => {
  // Prepare the form data for validation.
  const processedFormData = [];
  Array.from(elements).forEach((item) => {
    processedFormData[item.name] = item.value;
  });
};

/**
 * Utility function to convert the drupal data in Magento API format.
 *
 * @param {array} processedData
 *   The form data in drupal format.
 *
 * @return {array}
 *   The form data in Magento API format.
 */
const getDataInMagentoFormat = (processedData, defaultValues, addressFields) => {
  const { userDetails } = drupalSettings;
  let customerData = [];
  if (hasValue(userDetails)) {
    // Extract the firstname and lastname.
    let name = extractFirstAndLastName(userDetails.userName);
    customerData['firstname'] = name.firstname;
    customerData['lastname'] = name.lastname;
    customerData['email'] = userDetails.userEmailID;

    // Now prepare the address array.
    name = extractFirstAndLastName(processedData.full_name);
    customerData['address']['firstname'] = name.firstname;
    customerData['address']['lastname'] = name.lastname;
    customerData['address']['telephone'] = processedData.mobile;
    // Any new address will be considered as the default address.
    customerData['address']['default_billing'] = true;
    customerData['address']['default_shipping'] = true;

    // Traverse the address fields and update the same in customerData.
    addressFields.forEach((item, key) => {

    });
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
  if (hasValue(drupalSettings.country_name)) {
    valueOfAddressItems.country = drupalSettings.country_name;
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

export {
  getProcessedFormData,
  getDeliveryInfo,
  getDataInMagentoFormat,
};
