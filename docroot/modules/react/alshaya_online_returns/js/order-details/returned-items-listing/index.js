import React from 'react';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import { getTypeFromReturnItem } from '../../utilities/order_details_util';
import { isReturnClosed, isReturnPicked } from '../../utilities/return_api_helper';
import ReturnedItems from './returned-items';

class ReturnedItemsListing extends React.Component {
  getReturnedItemsSubTitle = (type) => {
    if (type === 'online') {
      return Drupal.t('Online Returns', {}, { context: 'online_returns' });
    }

    if (type === 'store') {
      return Drupal.t('Store Returns', {}, { context: 'online_returns' });
    }

    if (type === 'rejected') {
      return Drupal.t('Rejected Items', {}, { context: 'online_returns' });
    }

    return '';
  };

  getReturnedItemsTitle = (type) => {
    if (type === 'online' || type === 'store') {
      return Drupal.t('Returned Items', {}, { context: 'online_returns' });
    }

    if (type === 'rejected') {
      return Drupal.t('Online Returns', {}, { context: 'online_returns' });
    }

    return '';
  }

  // To filter out items that are rejected.
  getRejectedFilteredItems = (returnItem, rejectedItems) => {
    // Storing in a separate variable as function param cannot be updated.
    const updatedReturnItem = returnItem;

    updatedReturnItem.items = returnItem.items.filter((item) => !(hasValue(
      rejectedItems[returnItem.returnInfo.increment_id],
    ) && hasValue(rejectedItems[returnItem.returnInfo.increment_id][item.item_id])));

    return updatedReturnItem;
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
    const rejectedItems = {};

    // Filter out all the closed & picked returns.
    const updateResults = returns.filter((item) => isReturnClosed(item.returnInfo)
      && isReturnPicked(item.returnInfo));

    updateResults.forEach((returnItem) => {
      // Filter out the items which are rejected.
      returnItem.returnInfo.items.forEach((item) => {
        const { qty_rejected: qtyRejected } = item.extension_attributes;
        if (qtyRejected > 0) {
          // Initialize the empty array for rejected key.
          if (!rejectedItems[returnItem.returnInfo.increment_id]) {
            rejectedItems[returnItem.returnInfo.increment_id] = {};
          }
          rejectedItems[returnItem.returnInfo.increment_id][item.order_item_id] = returnItem;
        }
      });

      const itemReturnType = getTypeFromReturnItem(returnItem);
      // Check if return type is initialized or not.
      if (!groupedItems[itemReturnType]) {
        groupedItems[itemReturnType] = [];
      }
      // Push the item in the return type group.
      groupedItems[itemReturnType].push(this.getRejectedFilteredItems(
        { ...returnItem }, rejectedItems,
      ));

      // Build the rejected item list.
      returnItem.items.forEach((item, index) => {
        if (hasValue(rejectedItems[returnItem.returnInfo.increment_id])
          && rejectedItems[returnItem.returnInfo.increment_id][item.item_id]) {
          // Store the rejected items in grouped items.
          if (!groupedItems.rejected) {
            groupedItems.rejected = [];
          }

          groupedItems.rejected[index] = { ...returnItem };
          groupedItems.rejected[index].items = [];
          groupedItems.rejected[index].items.push({ ...returnItem.items[index] });
        }
      });
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
                returnType={index}
              />
            ))}
          </div>
        ))}
      </div>
    );
  }
}

export default ReturnedItemsListing;
