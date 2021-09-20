import React from 'react';
import HomeDeliverySVG from '../../../../../alshaya_pdp_react/js/svg-component/hd-svg';
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

  showExpressDeliveryBlock = () => {
    const { open } = this.state;

    if (open) {
      this.setState({
        open: false,
      });
      this.expandRef.current.classList.add('close-card');
    } else {
      this.setState({
        open: true,
      });
      this.expandRef.current.classList.remove('close-card');
    }
  };

  render() {
    const { expressDelivery } = drupalSettings;
    const { open, shippingMethods, panelContent } = this.state;
    // Add correct class.
    const expandedState = open === true ? 'show' : '';
    // If expressDelivery is not set we exit.
    if (expressDelivery === undefined) {
      return null;
    }
    if (shippingMethods === null) {
      return null;
    }
    return (
      <div
        className="pdp-express-delivery-wrapper card fadeInUp"
        style={{ animationDelay: '1s' }}
        ref={this.expandRef}
      >
        <div
          className={`express-delivery-title-wrapper title ${expandedState}`}
          onClick={() => this.showExpressDeliveryBlock()}
        >
          <div className="express-delivery-title">
            <span className="card-icon-svg">
              <HomeDeliverySVG />
            </span>
            {expressDelivery.title}
          </div>
          <div className="accordion" />
        </div>
        <div className="content express-delivery-detail">
          <span>{expressDelivery.subtitle}</span>
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
      </div>
    );
  }
}
