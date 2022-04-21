import React from 'react';
import ConditionalView from '../../../../js/utilities/components/conditional-view';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../js/utilities/showRemoveFullScreenLoader';
import { processReturnData } from '../../utilities/order_details_util';
import { getReturnsByOrderId } from '../../utilities/return_api_helper';
import ReturnedItems from './returned-items';

class ReturnedItemsListing extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      returns: null,
    };
  }

  componentDidMount() {
    this.getReturns();
  }

  /**
   * Get returns.
   */
  getReturns = async () => {
    const { orderEntityId } = drupalSettings.onlineReturns;

    showFullScreenLoader();
    const returns = await getReturnsByOrderId(orderEntityId);
    removeFullScreenLoader();

    if (hasValue(returns) && hasValue(returns.data) && hasValue(returns.data.items)) {
      this.setState({
        returns: processReturnData(returns.data.items),
      });
    }
  }

  getReturnedItemsSubTitle = (type) => {
    if (type === 'online') {
      return Drupal.t('Online Returns', {}, { context: 'online_returns' });
    }

    if (type === 'store') {
      return Drupal.t('Store Returns', {}, { context: 'online_returns' });
    }

    return '';
  };

  getReturnsByType = (type) => {
    const { returns } = this.state;

    // @todo: Update this code to filter out returns based on type.
    if (type === 'online') {
      return returns;
    }

    return returns;
  };

  render() {
    const { returns } = this.state;

    if (!hasValue(returns)) {
      return null;
    }

    return (
      <div className="returned-items-wrapper">
        <ConditionalView condition={hasValue(this.getReturnsByType('online'))}>
          <ReturnedItems
            key="online"
            subTitle={this.getReturnedItemsSubTitle('online')}
            returns={returns}
          />
        </ConditionalView>
      </div>
    );
  }
}

export default ReturnedItemsListing;
