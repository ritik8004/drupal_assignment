import React from 'react';

const LinkYourCardMessage = () => (
  <div className="spc-aura-link-your-card-message">
    {Drupal.t('To enable auto accrual and rewards redemption')}
    <span>
      {Drupal.t('link your card now')}
    </span>
  </div>
);

export default LinkYourCardMessage;
