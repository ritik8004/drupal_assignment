import React from 'react';
import ConditionalView from '../../../../common/components/conditional-view';
import ToolTip from '../../../../utilities/tooltip';

const cvvText = Drupal.t('This code is a three or four digit number printed on the front or back of the credit card');

const SelectedCard = ({
  cardInfo, openStoreListModal, labelEffect, handleCardCvvChange, onExistingCardSelect, selected = true,
}) => (
  <div>
    <span onClick={() => onExistingCardSelect(cardInfo.public_hash)}>
      {Drupal.t('card no.') + ' ' + cardInfo.maskedCC}
      {Drupal.t('expires') +  ' ' + cardInfo.expirationDate}
    </span>
    <div className="spc-add-new-address-btn" onClick={openStoreListModal}>
      {Drupal.t('change')}
    </div>
    <ConditionalView condition={selected && cardInfo.mada === true}>
      <div className="spc-type-textfield spc-type-cvv">
        <input
          type="tel"
          id="spc-cc-cvv"
          pattern="\d{3,4}"
          required
          onChange={handleCardCvvChange}
          onBlur={(e) => labelEffect(e, 'blur')}
        />
        <div className="c-input__bar" />
        <label htmlFor="spc-cc-cvv">{Drupal.t('CVV')}</label>
        <div id="spc-cc-cvv-error" className="error" />
        <ToolTip content={cvvText} enable question/>
      </div>
    </ConditionalView>
  </div>
);

export default SelectedCard;
