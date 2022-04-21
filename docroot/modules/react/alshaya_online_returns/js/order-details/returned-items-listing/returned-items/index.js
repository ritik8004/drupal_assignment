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
      </span>
      <span>
        {subTitle}
      </span>
    </div>
    <ConditionalView condition={hasValue(returns)}>
      {returns.map((returnData) => (
        <>
          <div>Refunded message</div>
          <div className="return-id">
            {Drupal.t('Return ID', {}, { context: 'online_returns' })}
            {':'}
            {returnData.returnId}
          </div>

          <ConditionalView condition={hasValue(returnData.items)}>
            {returnData.items.map((item) => (
              <div className="item-list-wrapper">
                <ReturnIndividualItem
                  key={item.id}
                  item={item}
                />
              </div>
            ))}
          </ConditionalView>
        </>
      ))}
    </ConditionalView>
  </div>
);

export default ReturnedItems;
