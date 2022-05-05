import React from 'react';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import ReturnIndividualItem from '../../../return-request/components/return-individual-item';

const ReturnedItems = ({
  subTitle,
  returns,
}) => (
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
        <>
          {/* @todo: Add refunded message here. */}
          <div className="return-id">
            {Drupal.t('Return ID: @return_id', { '@return_id': returnData.returnId }, { context: 'online_returns' })}
          </div>
          <ConditionalView condition={hasValue(returnData.items)}>
            {returnData.items.map((item) => (
              <div className="item-list-wrapper">
                <ReturnIndividualItem key={item.id} item={item} />
              </div>
            ))}
          </ConditionalView>
        </>
      ))}
    </ConditionalView>
  </div>
);

export default ReturnedItems;
