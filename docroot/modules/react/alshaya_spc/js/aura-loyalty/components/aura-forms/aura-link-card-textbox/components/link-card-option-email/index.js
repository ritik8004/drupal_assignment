import React from 'react';
import TextField from '../../../../../../utilities/textfield';

const LinkCardOptionEmail = (props) => {
  const { email, modal } = props;

  if (modal) {
    return (
      <TextField
        type="email"
        required
        name="email"
        label={Drupal.t('Email address')}
      />
    );
  }

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

export default LinkCardOptionEmail;
