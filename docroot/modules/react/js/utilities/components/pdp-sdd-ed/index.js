import React from 'react';
import { checkShippingMethodsStatus } from '../../expressDeliveryHelper';

export default class PdpSddEd extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      // Store shipping method details including express-delivery.
      expressDeliveryLabels: null,
    };

    // Get express-delivery settings.
    ({ expressDelivery: this.expressDelivery } = drupalSettings);
  }

  componentDidMount() {
    // Show SDD / ED labels on PDP
    document.addEventListener('showPdpSddEdLabel', this.showPdpSddEdLabel, false);
  }

  componentWillUnmount() {
    document.removeEventListener('showPdpSddEdLabel', this.showPdpSddEdLabel, false);
  }

  /**
   * Show SDD-ED labels on PDP.
   */
  showPdpSddEdLabel = (e) => {
    // Get shipping methods from event details.
    const { applicable_shipping_methods: shippingMethods } = e.detail;

    const {
      deliveryOptionsLabel,
      deliveryOptionsOrder,
    } = this.expressDelivery;

    const expressDeliveryLabels = [];

    // Toggle SDD ED label display based on status received.
    if (checkShippingMethodsStatus(shippingMethods)) {
      shippingMethods.forEach((shippingMethod) => {
        if (shippingMethod.available !== 'undefined' && shippingMethod.available) {
          if ((shippingMethod.carrier_code === 'SAMEDAY') || (shippingMethod.carrier_code === 'EXPRESS')) {
            const deliveryOptionKey = shippingMethod.carrier_title.toLowerCase().replaceAll(' ', '_');
            expressDeliveryLabels.push({
              key: deliveryOptionKey,
              value: deliveryOptionsLabel[deliveryOptionKey],
            });
          }
        }
      });

      // Sort labels as per settings.
      if (expressDeliveryLabels.length > 1) {
        // eslint-disable-next-line max-len
        expressDeliveryLabels.sort((a, b) => deliveryOptionsOrder.indexOf(a.key) > deliveryOptionsOrder.indexOf(b.key));
      }
    }

    this.setState({
      expressDeliveryLabels,
    });
  }

  render() {
    const {
      expressDeliveryLabels,
    } = this.state;

    if (expressDeliveryLabels === null) {
      return (null);
    }

    let sddEdLabels = [];
    sddEdLabels = expressDeliveryLabels.map((sddEdLabel) => (
      <div
        key={sddEdLabel.key}
        className={`express-delivery-text ${sddEdLabel.key} active`}
      >
        <span>{sddEdLabel.value}</span>
      </div>
    ));


    return (
      <div className="express-delivery active">
        {sddEdLabels}
      </div>
    );
  }
}
