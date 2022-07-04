import React from 'react';
import { checkShippingMethodsStatus } from '../../../js/utilities/expressDeliveryHelper';

export default class PdpSddEd extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      shippingMethods: null,
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
    const { shippingMethods } = this.state;

    if (shippingMethods === null) {
      return shippingMethods;
    }

    const sddEdLabels = [];
    if (shippingMethods !== null) {
      shippingMethods.forEach((shippingMethod) => {
        if (shippingMethod.available !== 'undefined' && shippingMethod.available) {
          if ((shippingMethod.carrier_code === 'SAMEDAY') || (shippingMethod.carrier_code === 'EXPRESS')) {
            sddEdLabels.push(shippingMethod);
          }
        }
      });
    }

    return (
      <div className="express-delivery active">
        {sddEdLabels && sddEdLabels !== null
        && sddEdLabels.length > 0
        && sddEdLabels.map((sddEdLabel) => (
          <div
            key={sddEdLabel.carrier_code}
            className={`express-delivery-text ${sddEdLabel.carrier_title.toLowerCase().replaceAll(' ', '_')} active`}
          >
            <span>{`${sddEdLabel.carrier_title} ${Drupal.t('Available')}`}</span>
          </div>
        ))}
      </div>
    );
  }
}
