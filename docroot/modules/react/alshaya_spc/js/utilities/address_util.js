import axios from 'axios';

/**
 * Get the address list of the current logged in user.
 */
export const getUserAddressList = function () {
  return axios.get('user-address-list')
    .then(response => {
      return response.data
    })
    .catch(error => {
      // Processing of error here.
    });
}

/**
 * Update default address for the user.
 *
 * @param {*} address_id
 */
export const updateUserDefaultAddress = function (address_id) {
  return axios.post('set-default-address', {
      'address_id': address_id
    })
    .then(response => {
      return response.data
    })
    .catch(error => {
      // Processing of error here.
    });
}

/**
 * Deletes address for the user.
 *
 * @param {*} address_id
 */
export const deleteUserAddress = function (address_id) {
  return axios.post('delete-address', {
      'address_id': address_id
    })
    .then(response => {
      return response.data
    })
    .catch(error => {
      // Processing of error here.
    });
}
