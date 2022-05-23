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

  render() {
    const { returns } = this.props;

    if (!hasValue(returns)) {
      return null;
    }

    return (
      <div className="returned-items-row returned-items">
        {returns.map((returnItem) => (
          <ConditionalView condition={hasValue(getTypeFromReturnItem(returnItem))
            && isReturnClosed(returnItem.returnInfo)}
          >
            <div className="title-wrapper">
              <span>
                {Drupal.t('Returned Items', {}, { context: 'online_returns' })}
                {' '}
                {'-'}
                {' '}
                {this.getReturnedItemsSubTitle(getTypeFromReturnItem(returnItem))}
              </span>
            </div>

            <ReturnedItems
              key={getTypeFromReturnItem(returnItem)}
              returnData={returnItem}
            />
          </ConditionalView>
        ))}
      </div>
    );
  }
}

export default ReturnedItemsListing;
