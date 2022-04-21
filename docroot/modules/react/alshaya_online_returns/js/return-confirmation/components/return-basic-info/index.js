import React from 'react';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import { formatDateTime } from '../../../utilities/online_returns_util';

const ReturnBasicInfo = ({
  returnData,
}) => {
  if (!hasValue(returnData)) {
    return null;
  }
  const returnDate = formatDateTime(returnData.date_requested);
  return (
    <div className="return-id-info">
      <span className="return-id-label">{ Drupal.t('Return ID', {}, { context: 'online_returns' }) }</span>
      <span className="return-id-value">{returnData.increment_id}</span>
      <ConditionalView condition={hasValue(returnDate)}>
        <span className="return-request-date">{returnDate}</span>
      </ConditionalView>
    </div>
  );
};

export default ReturnBasicInfo;
