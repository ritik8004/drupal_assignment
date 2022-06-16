import React from 'react';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import { isReturnClosed } from '../../utilities/return_api_helper';
import ProcessedItem from './processed-item';

const ReturnInitiated = ({
  returns,
  handleErrorMessage,
}) => {
  if (!hasValue(returns)) {
    return null;
  }

  const initiatedReturnItem = returns.find((returnItem) => !isReturnClosed(returnItem.returnInfo));

  if (!hasValue(initiatedReturnItem)) {
    return null;
  }
  return (
    <div className="return-initiated-item">
      <ProcessedItem
        returnData={initiatedReturnItem}
        handleErrorMessage={handleErrorMessage}
      />
    </div>
  );
};

export default ReturnInitiated;
