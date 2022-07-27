import React from 'react';
import getStringMessage from '../../../../../../../js/utilities/strings';

const AuraFormEmailField = (props) => {
  const { email } = props;

  return (
    <input
      type="email"
      id="spc-aura-link-card-input-email"
      name="spc-aura-link-card-input-email"
      className="spc-aura-link-card-input-email spc-aura-link-card-input"
      placeholder={getStringMessage('email_address')}
      defaultValue={email}
    />
  );
};

export default AuraFormEmailField;
