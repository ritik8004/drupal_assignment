import React from 'react';
import { hasValue } from '../../../../../../../js/utilities/conditionsUtility';
import getStringMessage from '../../../../../../../js/utilities/strings';

const MembershipExpiryInfo = ({
  pointTotal,
  expiryDate,
}) => {
  if (!hasValue(pointTotal) || !hasValue(expiryDate)) {
    return null;
  }

  return (
    <div className="member-expiry-block">
      <p className="expiry-point">
        {Drupal.t('@point_total @points_label', { '@point_total': pointTotal, '@points_label': getStringMessage('points_label') }, { context: 'hello_member' })}
      </p>
      <p>{getStringMessage('membership_renew_message', { '@expiry_date': expiryDate })}</p>
    </div>
  );
};

export default MembershipExpiryInfo;
