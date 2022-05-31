import React from 'react';
import getStringMessage from '../../../../../../js/utilities/strings';

const MembershipInfo = () => (
  <div className="hello-membership-info">
    <div className="hello-membership-title">
      {getStringMessage('membership_title')}
    </div>
    <div className="hello-membership-details">
      <p>{getStringMessage('membership_sub_title')}</p>
      <p>{getStringMessage('membership_details')}</p>
    </div>
  </div>
);

export default MembershipInfo;
