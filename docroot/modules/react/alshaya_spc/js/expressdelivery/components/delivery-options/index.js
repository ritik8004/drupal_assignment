import React from 'react';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../utilities/checkout_util';
import { getCartShippingMethods } from '../../../utilities/delivery_area_util';
import { getStorageInfo } from '../../../utilities/storage';
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
    const currentArea = getStorageInfo('deliveryinfo-areadata');
    const attr = document.getElementsByClassName('sku-base-form');
    const productSku = attr[0].getAttribute('data-sku');
    showFullScreenLoader();
    getCartShippingMethods(currentArea, productSku).then(
      (response) => {
        if (response !== null) {
          this.checkShippingMethods(response, productSku);
        }
        removeFullScreenLoader();
      },
    );
    document.addEventListener('handleAreaSelect', this.handleAreaSelect);
  }

  checkShippingMethods = (response, productSku) => {
    if (Array.isArray(response) && response.length !== 0) {
      const shippingMethodObj = response.find(
        (element) => element.product_sku === productSku,
      );
      if (shippingMethodObj && Object.keys(shippingMethodObj).length !== 0) {
        this.setState({
          shippingMethods: shippingMethodObj.applicable_shipping_methods,
        });
      }
    }
  }

  getPanelData = (data) => {
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
    if (shippingMethods === null) {
      return null;
    }
    return (
      <div className="product-delivery-options">
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
