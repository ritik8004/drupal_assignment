import React from 'react';
import ConditionalView from '../../../../js/utilities/components/conditional-view';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import { isReturnClosed } from '../../utilities/return_api_helper';
import ProcessedItem from './processed-item';

const ReturnInitiated = ({
  returns,
}) => {
  if (!hasValue(returns)) {
    return null;
  }
  return (
    <div className="return-processed-items">
      <ConditionalView condition={hasValue(returns)}>
        {returns.map((returnItem) => (
          <div className="return-items" key={returnItem.returnInfo.increment_id}>
            <ConditionalView condition={!isReturnClosed(returnItem.returnInfo)}>
              <ProcessedItem
                returnData={returnItem}
              />
            </ConditionalView>
          </div>
        ))}
      </ConditionalView>
    </div>
  );
};

export default ReturnInitiated;
