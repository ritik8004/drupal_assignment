import { hasValue } from '../../utilities/conditionsUtility';
import { getApiEndpoint } from '../../../alshaya_spc/js/backend/v2/utility';
import logger from '../../../alshaya_spc/js/utilities/logger';
import StaticStorage from '../../../alshaya_spc/js/backend/v2/staticStorage';
import { callMagentoApiSynchronous } from '../../../alshaya_spc/js/backend/v2/common';

const Tabby = {
  isTabbyEnabled: () => hasValue(drupalSettings.tabby)
    && hasValue(drupalSettings.tabby.widgetInfo),

  isAvailable: () => window.Tabby,

  productAvailable: (that) => {
    const { cart } = that.props;
    let tabbyStatus = StaticStorage.get('tabbyStatus');
    if (tabbyStatus && tabbyStatus[cart.cart.cart_total]) {
      return tabbyStatus[cart.cart.cart_total];
    }
    if (!tabbyStatus) {
      tabbyStatus = [];
    }
    tabbyStatus[cart.cart.cart_total] = [];
    // Get available methods from MDC.
    const response = callMagentoApiSynchronous(getApiEndpoint('getTabbyAvailableProducts', { cartId: window.commerceBackend.getCartId() }));
    tabbyStatus[cart.cart.cart_total].status = 'disabled';
    if (hasValue(response.available_products)) {
      const { installment } = response.available_products;
      if (installment.is_available) {
        tabbyStatus[cart.cart.cart_total].status = 'enabled';
      } else {
        tabbyStatus[cart.cart.cart_total].rejection_reason = hasValue(installment.rejection_reason) ? installment.rejection_reason : '';
      }
    }

    if (hasValue(response.statusText) && response.statusText === 'error') {
      logger.error('Error while fetching tabby available products. Error message: @message, Code: @errorCode.', {
        '@message': response.responseText,
        '@errorCode': response.status,
      });
    }
    StaticStorage.set('tabbyStatus', tabbyStatus);
    return tabbyStatus[cart.cart.cart_total];
  },
};

export default Tabby;
