import React from 'react';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import ProcessedItems from './processed-items';

const ReturnInitiated = ({
  returns,
}) => {
  if (!hasValue(returns)) {
    return null;
  }

  // @todo: Filtering of return items as per returnId and statuses.
  // @todo: Get returnStatus and returnStatusMessage from api result.
  return (
    <div className="return-initiated-items">
      <ProcessedItems
        returnStatus={Drupal.t('Refund Initiated')}
        returnMessage={Drupal.t('The courier will be assigned within 1-2 days', { context: 'online_returns' })}
        returns={returns}
      />
    </div>
  );
};

export default ReturnInitiated;
