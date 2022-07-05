import React from 'react';
import { checkShippingMethodsStatus } from '../../expressDeliveryHelper';

export default class PdpSddEd extends React.Component {
  constructor(props) {
    super(props);

    const { expressDelivery } = drupalSettings;
    const {
      deliveryOptionsOrder,
      deliveryOptionsLabel,
    } = expressDelivery;

    this.state = {
      shippingMethods: null,
      deliveryOptionsOrder,
      deliveryOptionsLabel,
    };
  }

  componentDidMount() {
    // Show SDD / ED labels on PDP
    document.addEventListener('showPdpSddEdLabel', this.showPdpSddEdLabel, false);
  }


  /**
   * Show SDD-ED labels on PDP.
   */
  showPdpSddEdLabel = (e) => {
    // Get shipping methods from event details.
    const { applicable_shipping_methods: shippingMethods } = e.detail;

    // Toggle SDD ED label display based on status received.
    if (checkShippingMethodsStatus(shippingMethods)) {
      this.setState({
        shippingMethods,
      });
    }
  }

  render() {
    const {
      shippingMethods,
      deliveryOptionsOrder,
      deliveryOptionsLabel,
    } = this.state;

    if (shippingMethods === null) {
      return (null);
    }

    const sddEdLabels = [];
    if (shippingMethods !== null) {
      shippingMethods.forEach((shippingMethod) => {
        if (shippingMethod.available !== 'undefined' && shippingMethod.available) {
          if ((shippingMethod.carrier_code === 'SAMEDAY') || (shippingMethod.carrier_code === 'EXPRESS')) {
            const deliveryOptionKey = shippingMethod.carrier_title.toLowerCase().replaceAll(' ', '_');
            sddEdLabels.push({
              key: deliveryOptionKey,
              value: deliveryOptionsLabel[deliveryOptionKey],
            });
          }
        }
      });
    }

    // Sort labels as per settings.
    if (sddEdLabels.length > 1) {
      // eslint-disable-next-line max-len
      sddEdLabels.sort((a, b) => deliveryOptionsOrder.indexOf(a.key) > deliveryOptionsOrder.indexOf(b.key));
    }

    return (
      <div className="express-delivery active">
        {sddEdLabels.length > 0 && sddEdLabels.map((sddEdLabel) => (
          <div
            key={sddEdLabel.key}
            className={`express-delivery-text ${sddEdLabel.key} active`}
          >
            <span>{sddEdLabel.value}</span>
          </div>
        ))}
      </div>
    );
  }
}
