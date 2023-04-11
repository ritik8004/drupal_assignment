import React from 'react';
import parse from 'html-react-parser';
import AuraAppLinks from '../aura-app-links';
import { isMobile } from '../../../../../../js/utilities/display';

const PendingEnrollmentMessage = () => {
  const message = isMobile()
    ? Drupal.t('To use your points online, please download the Aura MENA app and provide us with a few more details.', {}, { context: 'aura' })
    : parse(Drupal.t('To use your points online, please download the Aura MENA app available both on <strong>App Store</strong> and <strong>Play Store</strong>.', {}, { context: 'aura' }));

  return (
    <div className="spc-aura-pending-enrollment-message-wrapper">
      <div className="spc-aura-pending-enrollment-message">
        <div className="message">
          { message }
        </div>
        { isMobile() && <AuraAppLinks /> }
      </div>
    </div>
  );
};

export default PendingEnrollmentMessage;
