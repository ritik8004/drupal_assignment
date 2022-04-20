import React from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

const ReturnBasicInfo = ({
  returnData,
}) => {
  if (!hasValue(returnData)) {
    return null;
  }
  return (
    <div className="return-id-info">
      <span className="return-id-label">{ Drupal.t('Return ID', {}, { context: 'online_returns' }) }</span>
      <span className="return-id-value">{returnData.increment_id}</span>
      <span className="return-request-date">{returnData.date_requested}</span>
    </div>
  );
};

export default ReturnBasicInfo;
