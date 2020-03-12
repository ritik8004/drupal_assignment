import React from 'react';
import CardTypeSVG from '../../card-type-svg';

const SavedCardItem = ({ cardInfo, selected, onSelect }) => (
  <div className="payment-card">
    <div className="payment-card--data">
      <div className={`payment-card--info ${!selected ? '' : 'active'}`}>
        <div className="payment-card--number">
          &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;
          {cardInfo.maskedCC}
        </div>
        <div className="label">{Drupal.t('expires')}</div>
        <div className="payment-card--expiry">{ cardInfo.expirationDate }</div>
      </div>
      <div className={`payment-card--type ${cardInfo.paymentMethod.toLowerCase()}`}>
        <CardTypeSVG type={cardInfo.paymentMethod.toLowerCase()} class={`${cardInfo.paymentMethod.toLowerCase()} is-active`} />
      </div>
    </div>
    <div className="payment-card--options">
      <button type="button" onClick={() => onSelect(cardInfo.public_hash)}>{Drupal.t('Select')}</button>
    </div>
  </div>
);

export default SavedCardItem;
