import { hasValue } from '../../utilities/conditionsUtility';
import { callMagentoApi } from '../../../alshaya_spc/js/backend/v2/common';
import { getApiEndpoint } from '../../../alshaya_spc/js/backend/v2/utility';
import logger from '../../../alshaya_spc/js/utilities/logger';

const Tabby = {
  isTabbyEnabled: () => hasValue(drupalSettings.tabby)
    && hasValue(drupalSettings.tabby.widgetInfo),

  isAvailable: () => window.Tabby,

  productAvailable: (that) => {
    const { tabbyProductStatus } = that.state;
    if (!hasValue(tabbyProductStatus)) {
      // Get available methods from MDC.
      const params = {
        cartId: window.commerceBackend.getCartId(),
        paymentMethod: 'tabby',
      };
      callMagentoApi(getApiEndpoint('getTabbyAvailableProducts', params), 'GET', {})
        .then((response) => {
          if (hasValue(response.data) && hasValue(response.data.available_products)) {
            const status = hasValue(response.data.available_products.installment.is_available) ? 'enabled' : 'disabled';
            that.setState({
              tabbyProductStatus: status,
            });
          }
        })
        .catch((response) => {
          logger.error('Error while fetching tabby available products. Error message: @message, Code: @errorCode.', {
            '@message': hasValue(response.error) ? response.error.message : response,
            '@errorCode': hasValue(response.error) ? response.error.error_code : '',
          });
        });
    }
  },
};

export default Tabby;
