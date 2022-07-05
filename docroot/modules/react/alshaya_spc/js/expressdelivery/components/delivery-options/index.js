import React from 'react';
import { isExpressDeliveryEnabled, checkShippingMethodsStatus } from '../../../../../js/utilities/expressDeliveryHelper';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../utilities/checkout_util';
import { getCartShippingMethods, getDeliveryAreaStorage } from '../../../utilities/delivery_area_util';
import dispatchCustomEvent from '../../../utilities/events';
import PdpSelectArea from '../pdp-select-area';
import PdpShippingMethods from '../pdp-shipping-methods';
import SelectAreaPanel from '../select-area-panel';

export default class DeliveryOptions extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      shippingMethods: null,
      panelContent: null,
      isSddEdAvailableForProduct: false,
      checkSddEdAvailabilityStatus: true,
    };
  }

  componentDidMount() {
    this.fetchShippingMethods();
    // Updating shipping methods as per selection of variant.
    document.addEventListener('onSkuVariantSelect', this.updateShippingOnVariantSelect, false);
    document.addEventListener('displayShippingMethods', this.displayShippingMethods, false);
  }

  updateShippingOnVariantSelect = (e) => {
    // For express delivery, we only consider pdp in full view mode.
    if (e.detail && e.detail.data.sku && e.detail.data.viewMode === 'full') {
      this.fetchShippingMethods(e.detail.data.sku);
    }
  }

  checkShippingMethods = (response, productSku) => {
    if (Array.isArray(response) && response.length !== 0) {
      const shippingMethodObj = response.find(
        (element) => element.product_sku === productSku,
      );
      if (shippingMethodObj && Object.keys(shippingMethodObj).length !== 0) {
        this.setState({
          shippingMethods: shippingMethodObj.applicable_shipping_methods,
        }, () => {
          // Set accordion height for delivery options after content loads.
          dispatchCustomEvent('setDeliveryOptionAccordionHeight', {});
        });
      }
    }
  }

  displayShippingMethods = (event) => {
    event.preventDefault();
    this.fetchShippingMethods();
  }

  fetchShippingMethods = (variantSelected) => {
    const currentArea = getDeliveryAreaStorage();
    const attr = document.getElementsByClassName('sku-base-form');
    const productSku = variantSelected !== undefined ? variantSelected : attr[0].getAttribute('data-sku');
    const { checkSddEdAvailabilityStatus } = this.state;
    if (productSku && productSku !== null) {
      showFullScreenLoader();
      // fetch product level SSD/ED status only on initial load
      if (checkSddEdAvailabilityStatus) {
        getCartShippingMethods(null, productSku).then(
          (response) => {
            if (response && response !== null) {
              if (Array.isArray(response) && response.length !== 0) {
                const shippingMethodObj = response.find(
                  (element) => element.product_sku === productSku,
                );
                if (shippingMethodObj && Object.keys(shippingMethodObj).length !== 0) {
                  // Set default shipping methods so that
                  // If product does not support SSD/ED.
                  // Default methods will be shown.
                  this.setState({
                    shippingMethods: shippingMethodObj.applicable_shipping_methods,
                  });
                  // Check if SDD/ED is available on product level.
                  if (checkShippingMethodsStatus(shippingMethodObj.applicable_shipping_methods)) {
                    this.setState({
                      isSddEdAvailableForProduct: true,
                    });
                    // if products supports SSD/ED
                    // Show area based delivery Selection to user.
                    if (currentArea !== null) {
                      this.addShippingMethodWithArea(currentArea, productSku);
                    }
                  } else {
                    // Don't show DeliveryAreaSelect if product does notsupport
                    // SDD/ED on product level.
                    this.setState({
                      isSddEdAvailableForProduct: false,
                    });
                  }
                  // Setting check area availablity to false,
                  // to stop product level API call if user only
                  // Area change.
                  this.setState({
                    checkSddEdAvailabilityStatus: false,
                  });
                }

                // Dispatch event to show SDD / ED labels on PDP.
                dispatchCustomEvent('showPdpSddEdLabel', response[0]);
              }
            }
          },
        );
      } else {
        this.addShippingMethodWithArea(currentArea, productSku);
      }
      removeFullScreenLoader();
    }
  }

  addShippingMethodWithArea = (currentArea, productSku) => {
    getCartShippingMethods(currentArea, productSku).then(
      (responseWithArea) => {
        if (responseWithArea && responseWithArea !== null) {
          this.checkShippingMethods(responseWithArea, productSku);

          // Dispatch event to show SDD / ED labels on PDP.
          dispatchCustomEvent('showPdpSddEdLabel', responseWithArea[0]);
        }
      },
    );
  }

  getPanelData = (data) => {
    // Adds loading class for showing loader on onclick of delivery panel.
    document.querySelector('.delivery-loader').classList.add('loading');
    this.setState({
      panelContent: data,
    });
  };

  removePanelData = () => {
    this.setState({
      panelContent: null,
    });
  };

  render() {
    const { shippingMethods, panelContent, isSddEdAvailableForProduct } = this.state;
    // If expressDelivery is not enabled we exit.
    if (isExpressDeliveryEnabled() === false) {
      return null;
    }
    if (shippingMethods === null) {
      return null;
    }

    return (
      <div className="content express-delivery-detail">
        <PdpShippingMethods
          shippingMethods={shippingMethods}
        />
        <PdpSelectArea
          getPanelData={this.getPanelData}
          removePanelData={this.removePanelData}
          showCheckAreaAvailability={isSddEdAvailableForProduct}
        />
        <div className="select-area-popup-wrapper">
          <SelectAreaPanel
            panelContent={panelContent}
          />
        </div>
      </div>
    );
  }
}
