import React from 'react';

const AuraFormEmailField = (props) => {
  const { email } = props;

  return (
    <input
      type="email"
      id="spc-aura-link-card-input-email"
      name="spc-aura-link-card-input-email"
      className="spc-aura-link-card-input-email spc-aura-link-card-input"
      placeholder={Drupal.t('Email address')}
      defaultValue={email}
    />
  );
};

export default AuraFormEmailField;
