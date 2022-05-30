import React from 'react';
import ConditionalView from '../../../../js/utilities/components/conditional-view';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import { getTypeFromReturnItem } from '../../utilities/order_details_util';
import { isReturnClosed } from '../../utilities/return_api_helper';
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

  /**
   * Group the store and online return items.
   *
   * @returns {object}
   *   A object containing all the grouped items.
   */
  getReturnsByType = () => {
    const { returns } = this.props;
    const groupedItems = {};

    returns.forEach((item) => {
      // Check if return type is initialized or not.
      const itemReturnType = getTypeFromReturnItem(item);
      if (groupedItems[itemReturnType]) {
        groupedItems[itemReturnType].push(item);
      } else {
        groupedItems[itemReturnType] = [];
        groupedItems[itemReturnType].push(item);
      }
    });

    return groupedItems;
  };

  render() {
    const { returns } = this.props;

    if (!hasValue(returns)) {
      return null;
    }

    // Get all the items by grouping them into store and online returns.
    const groupedItems = this.getReturnsByType();

    return (
      <div className="returned-items-row returned-items">
        {Object.keys(groupedItems).map((index) => (
          <div className="items-wrapper">
            <div className="title-wrapper">
              <span>
                {Drupal.t('Returned Items', {}, { context: 'online_returns' })}
                {' '}
                {'-'}
                {' '}
                {this.getReturnedItemsSubTitle(index)}
              </span>
            </div>

            {groupedItems[index].map((returnItem) => (
              <ConditionalView condition={isReturnClosed(returnItem.returnInfo)}>
                <ReturnedItems
                  key={returnItem.returnInfo.increment_id}
                  returnData={returnItem}
                />
              </ConditionalView>
            ))}
          </div>
        ))}
      </div>
    );
  }
}

export default ReturnedItemsListing;
