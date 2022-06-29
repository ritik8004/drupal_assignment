import React from 'react';
import { checkShippingMethodsStatus } from '../../../../../js/utilities/expressDeliveryHelper';

export default class PdpSddEd extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      sddEdClass: 'in-active',
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
    const { applicable_shipping_methods: shippingMethods } = e.detail;
    if (checkShippingMethodsStatus(shippingMethods)) {
      this.setState({
        sddEdClass: 'active',
      });
    }
  }

  render() {
    const { sddEdClass } = this.state;

    const {
      productValues,
    } = this.props;

    const {
      expressDeliveryClass,
      deliveryOptions,
    } = productValues;

    return (
      <div className={`express-delivery ${expressDeliveryClass}`}>
        {deliveryOptions && deliveryOptions !== null
        && Object.keys(deliveryOptions).length > 0
        && Object.keys(deliveryOptions).map((option) => (
          <div
            key={option}
            className={`express-delivery-text ${option} ${sddEdClass}`}
          >
            <span>{deliveryOptions[option].label}</span>
          </div>
        ))}
      </div>
    );
  }
}
