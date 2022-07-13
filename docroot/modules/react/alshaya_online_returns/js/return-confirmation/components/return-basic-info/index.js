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
      <div className="return-id-label light">{ Drupal.t('Return ID', {}, { context: 'online_returns' }) }</div>
      <div className="return-id-value dark">{returnData.increment_id}</div>
      <ConditionalView condition={hasValue(returnDate)}>
        <div className="return-request-date light">{returnDate}</div>
      </ConditionalView>
    </div>
  );
};

export default ReturnBasicInfo;
