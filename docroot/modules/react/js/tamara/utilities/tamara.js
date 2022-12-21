import { hasValue } from '../../utilities/conditionsUtility';
import { getApiEndpoint } from '../../../alshaya_spc/js/backend/v2/utility';
import { callMagentoApiSynchronous } from '../../utilities/requestHelper';

const Tamara = {
  // Check if Tamara is enabled on the site or not.
  isTamaraEnabled: () => hasValue(drupalSettings.payment_methods)
    && Object.keys(drupalSettings.payment_methods).indexOf('tamara', 0) !== -1,

  // Verify if the Tamara payment option is available for the current cart or
  // not. For this, we need call an MDC API to get the Tamara availability.
  // This function will return TRUE/FALSE based on the availability status.
  isAvailable: (that) => {
    // Get the cart total to store the tamara status in Static Storage. If we
    // found an status for the cart total in Static storage, we will return from
    // there else we will make an API call to Magento to get the satus.
    const { cart } = that.props;
    const total = cart.cart.totals.base_grand_total_without_surcharge;

    // Check if the tamaraStatus for current cart value exist in the Static
    // Storage and return.
    let tamaraStatus = Drupal.alshayaSpc.staticStorage.get('tamaraStatus');
    if (tamaraStatus && typeof tamaraStatus[total] !== 'undefined') {
      return tamaraStatus[total];
    }

    // Reset the status variable to empty if tamara status is not available.
    if (!tamaraStatus) {
      tamaraStatus = [];
    }

    // Get availability from MDC for the current cart.
    const response = callMagentoApiSynchronous(
      getApiEndpoint(
        'getTamaraAvailability',
        { cartId: window.commerceBackend.getCartId() },
      ),
    );

    // Set the default tamara availability status to false for cart value.
    tamaraStatus[total] = false;

    // If `is_available` is set to '1', it means tamara payment option is
    // available for the current cart value. It also means that current cart
    // value falls in Tamara threshold limit.
    // If the payment option available, update the tamaraStatus variable for the
    //  cart value.
    if (hasValue(response.is_available)) {
      tamaraStatus[total] = true;
    }

    // We storage the statuc in Static storage to avoid multiple API calls.
    Drupal.alshayaSpc.staticStorage.set('tamaraStatus', tamaraStatus);

    // Return the tamara status from the Static Storage for the cart value.
    return tamaraStatus[total];
  },
};

export default Tamara;
