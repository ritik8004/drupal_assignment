import React from 'react';
import { hasValue } from '../../../../../../../js/utilities/conditionsUtility';
import getStringMessage from '../../../../../../../js/utilities/strings';

const MembershipExpiryInfo = ({
  expiryInfo,
}) => {
  if (!hasValue(expiryInfo)) {
    return null;
  }

  const expiryInfoData = JSON.parse(expiryInfo);
  return (
    <div className="member-expiry-block">
      <p className="expiry-point">
        {expiryInfoData.total}
        {' '}
        {getStringMessage('points_label')}
      </p>
      <p>{getStringMessage('membership_renew_message', { '@expiry_date': expiryInfoData.expiry_date })}</p>
    </div>
  );
};

export default MembershipExpiryInfo;
