import React from 'react';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import ProcessedItem from '../processed-item';

const ProcessedItems = ({
  returns,
  returnStatus,
  returnMessage,
}) => {
  if (!hasValue(returns)) {
    return null;
  }

  // @todo: Breaking/Grouping of return items as per returnId.
  // @todo: Items will be listed with specific return statuses.
  // @todo: Get returnStatus and returnStatusMessage from api result.
  return (
    <div className="return-processed-items">
      <ConditionalView condition={hasValue(returns)}>
        {returns.map((returnData) => (
          <div key={returnData.returnInfo.returnId} className="return-items-wrapper">
            <ProcessedItem
              returnData={returnData}
              returnStatus={returnStatus}
              returnMessage={returnMessage}
            />
          </div>
        ))}
      </ConditionalView>
    </div>
  );
};
export default ProcessedItems;
