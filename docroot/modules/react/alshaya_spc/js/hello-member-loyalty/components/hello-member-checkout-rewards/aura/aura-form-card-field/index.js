import React from 'react';
import Cleave from 'cleave.js/react';
import getStringMessage from '../../../../../../../js/utilities/strings';

const AuraFormCardField = (props) => {
  const { cardNumber } = props;

  return (
    <Cleave
      placeholder={getStringMessage('aura_accout_number')}
      id="spc-aura-link-card-input-card"
      name="spc-aura-link-card-input-card"
      className="spc-aura-link-card-input-card spc-aura-link-card-input"
      options={{ blocks: [4, 4, 4, 4] }}
      value={cardNumber}
      autoComplete="off"
    />
  );
};

export default AuraFormCardField;
