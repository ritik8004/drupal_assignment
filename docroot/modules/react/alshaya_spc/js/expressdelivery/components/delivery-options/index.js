import React from 'react';
import { isExpressDeliveryEnabled } from '../../../../../js/utilities/expressDeliveryHelper';
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
    };
  }

  componentDidMount() {
    const { variantSelected } = this.props;
    if (variantSelected !== undefined) {
      this.fetchShippingMethods(variantSelected);
    }
    // Updating shipping methods as per selection of variant.
    document.addEventListener('onSkuVariantSelect', this.updateShippingOnVariantSelect, false);
    document.addEventListener('displayShippingMethods', this.displayShippingMethods, false);
  }

  updateShippingOnVariantSelect = (e) => {
    if (e.detail && e.detail.data !== '') {
      this.fetchShippingMethods(e.detail.data);
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
    const { variantSelected } = this.props;
    let productSku = null;
    // Get product sku from props variable for new pdp.
    // And for old pdp, we are fetching from page html.
    if (variantSelected !== undefined) {
      productSku = variantSelected;
    } else if (document.querySelector('[name="selected_variant_sku"]') !== null) {
      productSku = document.querySelector('[name="selected_variant_sku"]').getAttribute('value');
    }
    this.fetchShippingMethods(productSku);
  }

  fetchShippingMethods = (productSku) => {
    if (productSku !== null) {
      const currentArea = getDeliveryAreaStorage();
      showFullScreenLoader();
      getCartShippingMethods(currentArea, productSku).then(
        (response) => {
          if (response !== null) {
            this.checkShippingMethods(response, productSku);
          }
          removeFullScreenLoader();
        },
      );
    }
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
    const { shippingMethods, panelContent } = this.state;
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
