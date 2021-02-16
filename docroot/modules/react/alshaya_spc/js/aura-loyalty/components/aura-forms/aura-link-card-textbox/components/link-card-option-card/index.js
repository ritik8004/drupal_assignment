import React from 'react';
import Cleave from 'cleave.js/react';
import getStringMessage from '../../../../../../utilities/strings';

const labelEffect = (e) => {
  if (e.currentTarget.value.length > 0) {
    e.currentTarget.classList.add('focus');
  } else {
    e.currentTarget.classList.remove('focus');
  }
};

const LinkCardOptionCard = (props) => {
  const { cardNumber, modal } = props;

  if (modal) {
    return (
      <div className="link-card-type-textfield spc-type-textfield">
        <Cleave
          id="spc-aura-link-card-input-card"
          name="spc-aura-link-card-input-card"
          className="spc-aura-link-card-input-card spc-aura-link-card-input"
          options={{ blocks: [4, 4, 4, 4] }}
          value={cardNumber}
          autoComplete="off"
          onBlur={(e) => labelEffect(e)}
        />
        <div className="c-input__bar" />
        <label>{Drupal.t('Card number')}</label>
        <div id="link-card-number-error" className="error" />
      </div>
    );
  }

  return (
    <Cleave
      placeholder={getStringMessage('loyalty_card_placeholder')}
      id="spc-aura-link-card-input-card"
      name="spc-aura-link-card-input-card"
      className="spc-aura-link-card-input-card spc-aura-link-card-input"
      options={{ blocks: [4, 4, 4, 4] }}
      value={cardNumber}
      autoComplete="off"
    />
  );
};

export default LinkCardOptionCard;
