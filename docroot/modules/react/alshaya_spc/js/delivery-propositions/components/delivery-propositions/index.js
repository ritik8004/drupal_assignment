import React from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import DeliveryPropositionItem from '../delivery-proposition-item';
import { callMagentoApi } from '../../../../../js/utilities/requestHelper';

class DeliveryPropositions extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      deliveryPropositionItems: [],
    };
  }

  componentDidMount() {
    // Fetch delivery proposition data.
    this.getDeliveryPropositionData();
  }

  /**
   * Helper function to fetch delivery proposition data.
   */
  getDeliveryPropositionData = async () => {
    const deliveryPropositionItems = [];
    // Get delivery propositions response.
    const response = await callMagentoApi(
      '/V1/checkout/get-delivery-proposition',
      'GET',
      {},
      false,
    );
    // Process response data if it is valid and expected response.
    if (typeof response.data !== 'undefined' && Array.isArray(response.data)) {
      response.data.forEach((responseData, key) => {
        deliveryPropositionItems.push(
          { ...responseData, key },
        );
      });
      // Set delivery propositions data in state.
      this.setState({
        deliveryPropositionItems,
      });
    }
  };

  render() {
    const { deliveryPropositionItems } = this.state;
    if (!hasValue(deliveryPropositionItems)) {
      return null;
    }

    return (
      <div className="delivery-propositions-wrapper">
        {
          deliveryPropositionItems.map((item) => (
            <DeliveryPropositionItem
              key={item.key}
              data={item}
            />
          ))
        }
      </div>
    );
  }
}

export default DeliveryPropositions;
