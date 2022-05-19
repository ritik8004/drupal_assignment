import React from 'react';
import getStringMessage from '../../../../../../js/utilities/strings';

const MembershipInfo = () => (
  <div className="hello-membership-info">
    <div className="hello-membership-title">
      {getStringMessage('membership_title')}
    </div>
    <div className="hello-membership-details">
      {getStringMessage('membership_details')}
    </div>
    <div className="hello-membership-info-button">
      {getStringMessage('membership_info_button')}
    </div>
    <div className="hello-membership-info-link">
      {getStringMessage('membership_info_link')}
    </div>
    <div className="hello-membership-terms-privacy">
      {Drupal.t('Read')}
      <span className="terms-link">{getStringMessage('membership_terms')}</span>
      {Drupal.t('and')}
      <span className="privacy-link">{getStringMessage('membership_privacy')}</span>
    </div>
  </div>
);

export default MembershipInfo;
