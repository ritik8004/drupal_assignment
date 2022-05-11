import React from 'react';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import ReturnIndividualItem from '../../../return-request/components/return-individual-item';

const ReturnedItems = ({
  subTitle,
  returns,
}) => (
  // Display the returned item section only if the `is_closed` flag is set as
  // true.
  <ConditionalView
    condition={hasValue(returns) && returns[0].returnInfo.extension_attributes.is_closed}
  >
    <div className="returned-items">
      <div className="title-wrapper">
        <span>
          {Drupal.t('Returned Items', {}, { context: 'online_returns' })}
          {' '}
          {'-'}
          {' '}
          {subTitle}
        </span>
      </div>
      <ConditionalView condition={hasValue(returns)}>
        {returns.map((returnData) => (
          <div key={returnData.returnInfo.increment_id} className="return-items-wrapper">
            <div className="return-status-wrapper">
              <div className="return-status">
                <span className="status-label">{returnData.returnInfo.extension_attributes.customer_status}</span>
                <span className="status-message">{returnData.returnInfo.extension_attributes.description}</span>
              </div>
            </div>
            <div className="return-id">
              {Drupal.t('Return ID: @return_id', { '@return_id': returnData.returnInfo.increment_id }, { context: 'online_returns' })}
            </div>
            <ConditionalView condition={hasValue(returnData.items)}>
              {returnData.items.map((item) => (
                <div className="item-list-wrapper">
                  <ReturnIndividualItem key={item.id} item={item} />
                </div>
              ))}
            </ConditionalView>
          </div>
        ))}
      </ConditionalView>
    </div>
  </ConditionalView>
);

export default ReturnedItems;
