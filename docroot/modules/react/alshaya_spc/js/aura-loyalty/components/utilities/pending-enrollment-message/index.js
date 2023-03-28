import React from 'react';
import parse from 'html-react-parser';
import AuraAppLinks from '../aura-app-links';
import { isMobile } from '../../../../../../js/utilities/display';
import ConditionalView from '../../../../../../js/utilities/components/conditional-view';

const PendingEnrollmentMessage = () => {
  const device = isMobile();
  let message = null;

  message = (device) ? Drupal.t('To use your points online, please download the Aura MENA app and provide us with a few more details.', {}, { context: 'aura' }) : parse(Drupal.t('To use your points online, please download the Aura MENA app available both on <strong>App Store</strong> and <strong>Play Store</strong>.', {}, { context: 'aura' }));

  return (
    <div className="spc-aura-pending-enrollment-message-wrapper">
      <div className="spc-aura-pending-enrollment-message">
        <div className="message">
          { message }
        </div>
        <ConditionalView condition={device}>
          <AuraAppLinks />
        </ConditionalView>
      </div>
    </div>
  );
};

export default PendingEnrollmentMessage;
