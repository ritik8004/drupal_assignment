import React from 'react';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import { getTypeFromReturnItem } from '../../utilities/order_details_util';
import { isReturnClosed, isReturnPicked } from '../../utilities/return_api_helper';
import ReturnedItems from './returned-items';

class ReturnedItemsListing extends React.Component {
  getReturnedItemsSubTitle = (type) => {
    if (type === 'online' || type === 'rejected') {
      return Drupal.t('Online Returns', {}, { context: 'online_returns' });
    }

    if (type === 'store') {
      return Drupal.t('Store Returns', {}, { context: 'online_returns' });
    }

    return '';
  };

  getReturnedItemsTitle = (type) => {
    if (type === 'online' || type === 'store') {
      return Drupal.t('Returned Items', {}, { context: 'online_returns' });
    }

    if (type === 'rejected') {
      return Drupal.t('Rejected Items', {}, { context: 'online_returns' });
    }

    return '';
  }

  /**
   * Group the store and online return items.
   *
   * @returns {object}
   *   A object containing all the grouped items.
   */
  getReturnsByType = () => {
    const { returns } = this.props;
    const groupedItems = {};

    // Filter out all the closed & picked returns.
    const updateResults = returns.filter((item) => isReturnClosed(item.returnInfo)
      && isReturnPicked(item.returnInfo));

    updateResults.forEach((item) => {
      const itemReturnType = getTypeFromReturnItem(item);
      // Check if return type is initialized or not.
      if (!groupedItems[itemReturnType]) {
        groupedItems[itemReturnType] = [];
      }
      // Push the item in the return type group.
      groupedItems[itemReturnType].push(item);
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
          <div key={index} className="items-wrapper">
            <div className="title-wrapper">
              <span>
                {this.getReturnedItemsTitle(index)}
                {' '}
                {'-'}
                {' '}
                {this.getReturnedItemsSubTitle(index)}
              </span>
            </div>

            {hasValue(groupedItems) && groupedItems[index].map((returnItem) => (
              <ReturnedItems
                key={returnItem.returnInfo.increment_id}
                returnData={returnItem}
              />
            ))}
          </div>
        ))}
      </div>
    );
  }
}

export default ReturnedItemsListing;
