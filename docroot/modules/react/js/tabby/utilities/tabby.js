import { hasValue } from '../../utilities/conditionsUtility';
import {
  getApiEndpoint,
  isRequestFromSocialAuthPopup,
} from '../../../alshaya_spc/js/backend/v2/utility';
import logger from '../../utilities/logger';
import { callMagentoApiSynchronous } from '../../utilities/requestHelper';

const Tabby = {
  isTabbyEnabled: () => hasValue(drupalSettings.tabby)
    && hasValue(drupalSettings.tabby.widgetInfo),

  showTabbyWidget: () => hasValue(drupalSettings.tabby)
    && drupalSettings.tabby.showTabbyWidget,

  isAvailable: () => window.Tabby,

  productAvailable: (that) => {
    // If request is from SocialAuth Popup, restrict further processing.
    // we don't want magento API calls happen on popup, As this is causing issues
    // in processing parent pages.
    if (isRequestFromSocialAuthPopup()) {
      return false;
    }

    const { cart } = that.props;
    let tabbyStatus = Drupal.alshayaSpc.staticStorage.get('tabbyStatus');
    const total = cart.cart.totals.base_grand_total_without_surcharge;
    if (tabbyStatus && typeof tabbyStatus[total] !== 'undefined') {
      return tabbyStatus[total];
    }
    if (!tabbyStatus) {
      tabbyStatus = [];
    }
    // Get available methods from MDC.
    const response = callMagentoApiSynchronous(getApiEndpoint('getTabbyAvailableProducts', { cartId: window.commerceBackend.getCartId() }));
    tabbyStatus[total] = false;
    if (hasValue(response.available_products)) {
      const { installment } = response.available_products;
      if (installment.is_available) {
        tabbyStatus[total] = true;
      }
      Drupal.alshayaSpc.staticStorage.set('tabbyStatus', tabbyStatus);
    }

    if (hasValue(response.statusText) && response.statusText === 'error') {
      logger.error('Error while fetching tabby available products. Error message: @message, Code: @errorCode.', {
        '@message': response.responseText,
        '@errorCode': response.status,
      });
    }
    return tabbyStatus[total];
  },
};

export default Tabby;
