import React from 'react';
import CheckoutMessage from '../../utilities/checkout-message';

const SASessionBanner = ({ agentName }) => (
  <CheckoutMessage
    type="smart-agent-session"
    context="smart-agent-session-banner"
  >
    <span className="message">{`${Drupal.t('Order assisted by ALX InStorE')}: ${agentName}`}</span>
    <span className="action smart-agent-end-transaction">{Drupal.t('End Transaction')}</span>
  </CheckoutMessage>
);

export default SASessionBanner;
