import React from 'react';
import { getApiEndpoint } from '../../../backend/v2/utility';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import DeliveryPropositionItem from './DeliveryPropositionItem';
import { callMagentoApi } from '../../../../../js/utilities/requestHelper';

class DeliveryPropositions extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      deliveryPropositionItems: null,
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
      getApiEndpoint('getDeliveryPropositions'),
      'GET',
      {},
      false,
    );
    // Process response data if it is valid and expected response.
    if (typeof response.data !== 'undefined' && Array.isArray(response.data)) {
      response.data.forEach((responseData, key) => {
        const data = { ...responseData };
        data.id = key;
        deliveryPropositionItems.push(data);
      });
    }
    // Set delivery propositions data in state.
    this.setState({
      deliveryPropositionItems,
    });
  };

  render() {
    const { deliveryPropositionItems } = this.state;
    if (!hasValue(deliveryPropositionItems)) {
      return '';
    }

    return (
      <div className="delivery-propositions-wrapper">
        {
          deliveryPropositionItems.map((item) => (
            <DeliveryPropositionItem
              key={item.id}
              data={item}
            />
          ))
        }
      </div>
    );
  }
}

export default DeliveryPropositions;
