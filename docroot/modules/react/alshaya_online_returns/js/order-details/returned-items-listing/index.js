import React from 'react';
import ConditionalView from '../../../../js/utilities/components/conditional-view';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import ReturnedItems from './returned-items';

class ReturnedItemsListing extends React.Component {
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
    const { returns } = this.props;

    // @todo: Update this code to filter out returns based on type.
    if (type === 'online') {
      return returns;
    }

    return returns;
  };

  render() {
    const { returns } = this.props;

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
