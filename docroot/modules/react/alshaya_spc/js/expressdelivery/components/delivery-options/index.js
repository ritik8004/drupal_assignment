import React from 'react';
import Collapsible from 'react-collapsible';
import HomeDeliverySVG from '../../../../../alshaya_pdp_react/js/svg-component/hd-svg';
import { isExpressDeliveryEnabled } from '../../../../../js/utilities/expressDeliveryHelper';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../utilities/checkout_util';
import { getCartShippingMethods, getDeliveryAreaStorage } from '../../../utilities/delivery_area_util';
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
    this.fetchShippingMethods();
    document.addEventListener('displayShippingMethods', this.displayShippingMethods, false);
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

  displayShippingMethods = (event) => {
    event.preventDefault();
    this.fetchShippingMethods();
  }

  fetchShippingMethods = () => {
    const currentArea = getDeliveryAreaStorage();
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
    // If expressDelivery is not enabled we exit.
    if (isExpressDeliveryEnabled() === false) {
      return null;
    }
    if (shippingMethods === null) {
      return null;
    }

    const PdpDeliveryOptionAccordion = (
      <div
        className="express-delivery-title-wrapper title"
      >
        <div className="express-delivery-title">
          <span className="card-icon-svg">
            <HomeDeliverySVG />
          </span>
          {Drupal.t('Delivery Options')}
        </div>
        <span className="express-delivery-subtitle">{Drupal.t('Explore the delivery options applicable to your area.')}</span>
      </div>
    );

    return (
      <div
        className="pdp-express-delivery-wrapper card"
        ref={this.expandRef}
      >
        <Collapsible trigger={PdpDeliveryOptionAccordion}>
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
        </Collapsible>

      </div>
    );
  }
}
