import { hasValue } from '../../utilities/conditionsUtility';
import { getApiEndpoint } from '../../../alshaya_spc/js/backend/v2/utility';
import logger from '../../utilities/logger';
import StaticStorage from '../../../alshaya_spc/js/backend/v2/staticStorage';
import { callMagentoApiSynchronous } from '../../utilities/requestHelper';

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
    // Get available methods from MDC.
    const response = callMagentoApiSynchronous(getApiEndpoint('getTabbyAvailableProducts', { cartId: window.commerceBackend.getCartId() }));
    tabbyStatus[cart.cart.cart_total] = false;
    if (hasValue(response.available_products)) {
      const { installment } = response.available_products;
      if (installment.is_available) {
        tabbyStatus[cart.cart.cart_total] = true;
      }
      StaticStorage.set('tabbyStatus', tabbyStatus);
    }

    if (hasValue(response.statusText) && response.statusText === 'error') {
      logger.error('Error while fetching tabby available products. Error message: @message, Code: @errorCode.', {
        '@message': response.responseText,
        '@errorCode': response.status,
      });
    }
    return tabbyStatus[cart.cart.cart_total];
  },
};

export default Tabby;
