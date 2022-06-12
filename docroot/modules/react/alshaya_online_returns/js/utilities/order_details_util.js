/* eslint-disable */

/**
 * Utility function to return the type of return.
 *
 * @param {object} returnItem
 *   The individual return item object.
 *
 * @returns {string}
 *   A string to tell the type of return.
 */
function getTypeFromReturnItem(returnItem) {
  const {
    is_online: isOnline,
  } = returnItem.returnInfo.extension_attributes;
  // Based on the `is_online` flag we will identify the type of return.
  if (isOnline) {
    return 'online';
  } else {
    return 'store';
  }
}

/**
 * Utility function to return the total refunded amount.
 *
 * @param {object} returns
 *   The object containing the return product info.
 *
 * @returns {float}
 *   A floating value having total refund amount.
 */
function getTotalRefundAmount(returns) {
  let totalAmount = 0;
  // Traverse through all the returned items and get the refunded amount.
  if (returns.length > 0) {
    returns.forEach((item) => {
      if (item.extension_attributes.hasOwnProperty('refund_amount')) {
        totalAmount += item.extension_attributes.refund_amount;
      }
    });
  }

  return totalAmount;
}

export {
  getTypeFromReturnItem,
  getTotalRefundAmount,
};
