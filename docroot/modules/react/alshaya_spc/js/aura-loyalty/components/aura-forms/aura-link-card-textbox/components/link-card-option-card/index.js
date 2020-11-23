import React from 'react';
import Cleave from 'cleave.js/react';

const LinkCardOptionCard = (props) => {
  const { cardNumber } = props;

  return (
    <Cleave
      placeholder={Drupal.t('Loyalty card number')}
      id="spc-aura-link-card-input-card"
      name="spc-aura-link-card-input-card"
      className="spc-aura-link-card-input-card spc-aura-link-card-input"
      options={{ blocks: [4, 4, 4, 4] }}
      value={cardNumber}
    />
  );
};

export default LinkCardOptionCard;
