import React from 'react';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import { getTypeFromReturnItem } from '../../utilities/order_details_util';
import { isReturnClosed, isReturnPicked } from '../../utilities/return_api_helper';
import ReturnedItems from './returned-items';

class ReturnedItemsListing extends React.Component {
  /**
   * To get the return sub title.
   *
   * @param {string} type
   *   The string define the type of return.
   *
   * @returns {string}
   *   Subtitle for the return type.
   */
  getReturnedItemsSubTitle = (type) => {
    if (type === 'online' || type === 'rejected') {
      return Drupal.t('Online Returns', {}, { context: 'online_returns' });
    }

    if (type === 'store') {
      return Drupal.t('Store Returns', {}, { context: 'online_returns' });
    }

    return '';
  };

  /**
   * To get the return group title.
   *
   * @param {string} type
   *   The string define the type of return.
   *
   * @returns {string}
   *   Group title that defines the return type.
   */
  getReturnedItemsTitle = (type) => {
    if (type === 'online' || type === 'store') {
      return Drupal.t('Returned Items', {}, { context: 'online_returns' });
    }

    if (type === 'rejected') {
      return (
        <span className={`${type}`}>
          {Drupal.t('Cancelled Items', {}, { context: 'online_returns' })}
        </span>
      );
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

    updateResults.forEach((returnItem) => {
      // Proceed only if the item length is positive.
      if (returnItem.items.length > 0) {
        const itemReturnType = getTypeFromReturnItem(returnItem);
        // Check if return type is initialized or not.
        if (!groupedItems[itemReturnType]) {
          groupedItems[itemReturnType] = [];
        }
        // Push the item in the return type group.
        groupedItems[itemReturnType].push(returnItem);
      }

      // Build the rejected item list.
      if (returnItem.rejectedItems.length > 0) {
        returnItem.rejectedItems.forEach((item) => {
          // Store the rejected items in grouped items.
          if (!groupedItems.rejected) {
            groupedItems.rejected = [];
          }

          // Update the items with rejected item list.
          const updateItem = { ...returnItem };
          updateItem.items = [{ ...item }];

          groupedItems.rejected.push(updateItem);
        });
      }
    });

    return groupedItems;
  };

  /**
   * Validates if the groupedItems contain valid items.
   *
   * @param {object} groupedItems
   *   An object containing all grouped items based on return type.
   *
   * @return {bool}
   *   True if we have non zero items else False.
   */
  groupWithItems = (groupedItems) => groupedItems.filter((el) => el.items.length > 0);

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
          this.groupWithItems(groupedItems[index]).length > 0 && (
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
          )
        ))}
      </div>
    );
  }
}

export default ReturnedItemsListing;
