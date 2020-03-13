import React from 'react';
import ConditionalView from '../../../../common/components/conditional-view';
import ToolTip from '../../../../utilities/tooltip';

const SelectedCard = ({
  cardInfo, openSavedCardListModal, labelEffect, handleCardCvvChange, onExistingCardSelect, selected = true,
}) => {
  const cvvText = Drupal.t('This code is a three or four digit number printed on the front or back of the credit card');
  return (
    <div className='spc-checkout-payment-saved-card-preview'>
      <div className='spc-checkout-payment-data' onClick={() => onExistingCardSelect(cardInfo.public_hash, cardInfo.mada)}>
        <div className='spc-checkout-payment-saved-card-number'>{Drupal.t('card no.') + ' **** **** **** ' + cardInfo.maskedCC}</div>
        <div className='spc-checkout-payment-saved-card-expiry'>{Drupal.t('expires') +  ' ' + cardInfo.expirationDate}</div>
      </div>
      <div className="spc-add-new-card-btn" onClick={openSavedCardListModal}>
        {Drupal.t('change')}
      </div>
      <ConditionalView condition={selected && (cardInfo.mada === true || drupalSettings.checkoutCom.always_3d)}>
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
          <ToolTip content={cvvText} enable question />
        </div>
      </ConditionalView>
    </div>
  );
}

export default SelectedCard;
