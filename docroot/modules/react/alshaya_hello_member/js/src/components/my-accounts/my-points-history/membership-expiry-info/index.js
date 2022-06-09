import React from 'react';
import getStringMessage from '../../../../../../../js/utilities/strings';

const MembershipExpiryInfo = ({ expiryInfo }) => (
  <div className="member-expiry-block">
    <p className="expiry-point">
      {expiryInfo.total_points}
      {' '}
      {getStringMessage('points_label')}
    </p>
    <p>{getStringMessage('membership_renew_message', { '@expiry_date': expiryInfo.expiry_date })}</p>
  </div>
);

export default MembershipExpiryInfo;
