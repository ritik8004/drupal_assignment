import React from 'react';

const EligibleForReturn = () => (
  <span>
    {Drupal.t('Free online returns within 14 days', {}, { context: 'online_returns' })}
  </span>
);

export default EligibleForReturn;
