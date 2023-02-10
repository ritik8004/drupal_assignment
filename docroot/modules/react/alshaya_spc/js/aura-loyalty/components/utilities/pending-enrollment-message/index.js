import React from 'react';
import AuraAppLinks from '../aura-app-links';

const PendingEnrollmentMessage = () => {
  const message = Drupal.t('To use your points online, please download the Aura MENA app and provide us with a few more details.', {}, { context: 'aura' });

  return (
    <div className="spc-aura-pending-enrollment-message-wrapper">
      <div className="spc-aura-pending-enrollment-message">
        <div className="message">
          {message}
        </div>
        <AuraAppLinks />
      </div>
    </div>
  );
};

export default PendingEnrollmentMessage;
