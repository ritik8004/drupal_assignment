import React from 'react';
import Popup from 'reactjs-popup';
import AreaListBlock from '../../../cart/components/area-list-block';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../utilities/checkout_util';
import { getCartShippingMethods } from '../../../utilities/delivery_area_util';
import { getStorageInfo } from '../../../utilities/storage';

const attr = document.getElementsByClassName('sku-base-form');
const productSku = attr[0].getAttribute('data-sku');

export default class DeliveryOptions extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      skuShippingMethods: '',
      isModelOpen: false,
      areaLabel: Drupal.t('Select Area'),
    };
  }

  componentDidMount() {
    const currentArea = getStorageInfo('deliveryinfo-areadata');
    showFullScreenLoader();
    getCartShippingMethods(currentArea, productSku).then(
      (response) => {
        if (response !== null) {
          this.setState({
            skuShippingMethods: response,
          });
        }
        removeFullScreenLoader();
      },
    );
    document.addEventListener('handleAreaSelect', this.handleAreaSelect);
    if (currentArea !== null) {
      const { currentLanguage } = drupalSettings.path;
      this.setState({
        areaLabel: currentArea.label[currentLanguage],
      });
    }
  }

  openModal = () => {
    document.body.classList.add('open-form-modal');

    this.setState({
      isModelOpen: true,
    });
  };

  closeModal = () => {
    document.body.classList.remove('open-form-modal');

    this.setState({
      isModelOpen: false,
    });
  };

  handleAreaSelect = (event) => {
    event.preventDefault();
    const currentArea = getStorageInfo('deliveryinfo-areadata');
    if (currentArea !== null) {
      const { currentLanguage } = drupalSettings.path;
      this.setState({
        areaLabel: currentArea.label[currentLanguage],
      });
    }
  }


  render() {
    const { skuShippingMethods, isModelOpen, areaLabel } = this.state;
    let shippingMethods = null;

    if (skuShippingMethods === null) {
      return null;
    }

    if (Array.isArray(skuShippingMethods) && skuShippingMethods.length !== 0) {
      const cartMethodsObj = skuShippingMethods.find(
        (element) => element.product_sku === productSku,
      );
      if (cartMethodsObj && Object.keys(cartMethodsObj).length !== 0) {
        shippingMethods = cartMethodsObj.applicable_shipping_methods;
      }
    }

    if (shippingMethods === null) {
      return null;
    }
    return (
      <div className="sku-cart-shipping-methods">
        {
          shippingMethods.map((shippingMethod) => (
            <div className={`cart-shipping-method ${shippingMethod.carrier_code.toString().toLowerCase()} ${shippingMethod.available ? 'active' : 'in-active'}`}>
              <span className="carrier-title">{shippingMethod.carrier_title}</span>
              <span className="method-title">{shippingMethod.method_title}</span>
            </div>
          ))
        }
        <div id="delivery-area-select">
          <div className="delivery-area-label">
            <span>{`${Drupal.t('Deliver to')}: `}</span>
            <span>{areaLabel}</span>
            <br />
            <span onClick={() => this.openModal()} className="delivery-area-button">
              Arrow
            </span>
            <Popup
              open={isModelOpen}
              className="spc-area-list-popup"
              closeOnDocumentClick={false}
              closeOnEscape={false}
            >
              <AreaListBlock
                closeModal={() => this.closeModal()}
              />
            </Popup>
          </div>
        </div>
      </div>

    );
  }
}
