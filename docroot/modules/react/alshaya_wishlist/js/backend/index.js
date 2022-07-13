import { hasValue } from '../../../js/utilities/conditionsUtility';
import { callMagentoApi } from '../../../js/utilities/requestHelper';
import logger from '../../../js/utilities/logger';

window.commerceBackend = window.commerceBackend || {};

/**
 * Get the wishlist information from the backend using API.
 *
 * @returns {Promise<object>}
 *   A promise object.
 */
window.commerceBackend.getWishlistFromBackend = async () => {
  // Call magento api to get the wishlist items of current logged in user.
  const response = await callMagentoApi('/V1/wishlist/me/items', 'GET');
  if (hasValue(response.data)) {
    if (hasValue(response.data.error)) {
      logger.warning('Error getting wishlist items. Response: @response', {
        '@response': JSON.stringify(response.data),
      });
    }
  }

  // Return response to perform necessary operation
  // from where this function called.
  return response;
};

/**
 * Adds/removes products from wishlist in backend using API.
 *
 * @param {object} data
 *   The data object to send in the API call.
 *
 * @returns {Promise<object>}
 *   A promise object.
 */
window.commerceBackend.addRemoveWishlistItemsInBackend = async (data, action) => {
  // Early return is no action is provided.
  if (typeof action === 'undefined') {
    return null;
  }

  let requestMethod = null;
  let requestUrl = null;
  let itemData = null;

  switch (action) {
    case 'addWishlistItem': {
      requestMethod = 'POST';
      requestUrl = '/V1/wishlist/me/item/add';

      // Prepare sku options if available to push in backend api.
      const skuOptions = [];
      if (typeof data.options !== 'undefined'
        && data.options.length > 0) {
        data.options.forEach((option) => {
          skuOptions.push({
            id: option.option_id,
            value: option.option_value,
          });
        });
      }

      // Prepare wishlist item to push in backend api.
      itemData = {
        items: [
          {
            sku: data.sku,
            options: skuOptions,
          },
        ],
      };
      break;
    }

    case 'removeWishlistItem':
      requestMethod = 'DELETE';
      requestUrl = `/V1/wishlist/me/item/${data.wishlistItemId}/delete`;
      break;

    case 'mergeWishlistItems':
      requestMethod = 'POST';
      requestUrl = '/V1/wishlist/me/item/add';
      itemData = { items: data };
      break;

    default:
      logger.critical('Endpoint does not exist for action: @action.', {
        '@action': action,
      });
  }

  // Call magento backend with the api details.
  const response = await callMagentoApi(requestUrl, requestMethod, itemData);

  // Log if there are errors in the response.
  if (hasValue(response.data) && hasValue(response.data.error)) {
    logger.warning('Error adding item to wishlist. Post: @post, Response: @response', {
      '@post': JSON.stringify(itemData),
      '@response': JSON.stringify(response.data),
    });
  }

  return response;
};

/**
 * Get the raw wishlist information from the backend using API.
 *
 * @returns {Promise<object>}
 *   A promise object.
 */
window.commerceBackend.getWishlistInfoFromBackend = async () => {
  // Call magento api to get the wishlist items of current logged in user.
  const response = await callMagentoApi('/V1/wishlist/me/get', 'GET');
  if (hasValue(response.data)) {
    if (hasValue(response.data.error)) {
      logger.warning('Error getting wishlist items. Response: @response', {
        '@response': JSON.stringify(response.data),
      });
    }
  }

  // Return response to perform necessary operation
  // from where this function called.
  return response;
};

/**
 * Get the shared wishlist information from the backend using API.
 *
 * @returns {Promise<object>}
 *   A promise object.
 */
window.commerceBackend.getSharedWishlistFromBackend = () => {
  // Call magento api to get the wishlist items from sharing code.
  const response = callMagentoApi(`/V1/wishlist/code/${drupalSettings.wishlist.sharedCode}/items`, 'GET');
  if (hasValue(response.data)) {
    if (hasValue(response.data.error)) {
      logger.warning('Error getting wishlist items. Response: @response', {
        '@response': JSON.stringify(response.data),
      });
    }
  }

  // Return response to perform necessary operation
  // from where this function called.
  return response;
};
