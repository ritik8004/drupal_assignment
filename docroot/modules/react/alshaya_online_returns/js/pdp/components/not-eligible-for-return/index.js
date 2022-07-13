import React from 'react';

const NotEligibleForReturn = () => (
  <span>
    { Drupal.t('Not eligible for Return', {}, { context: 'online_returns' }) }
  </span>
);

export default NotEligibleForReturn;
