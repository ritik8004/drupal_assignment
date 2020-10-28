import React from 'react';
import CardTypeSVG from '../../../../svg-component/card-type-svg';

const SavedCardItem = ({ cardInfo, selected, onSelect }) => (
  <div className={`payment-card ${!selected ? '' : 'active'}`}>
    <div className="payment-card--data">
      <div className="payment-card--info">
        <div className="payment-card--number">
          &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;
          {cardInfo.maskedCC}
        </div>
        <div className={`payment-card--type ${cardInfo.paymentMethod.toLowerCase()}`}>
          <CardTypeSVG type={cardInfo.paymentMethod.toLowerCase()} class={`${cardInfo.paymentMethod.toLowerCase()} is-active`} />
        </div>
      </div>
      <div className="payment-card--expiry">
        <span className="label">{Drupal.t('expires')}</span>
        <span className="payment-card--expiry">{ cardInfo.expirationDate }</span>
      </div>
    </div>
    <div className="payment-card--options">
      <button type="button" onClick={() => onSelect(cardInfo.public_hash, cardInfo.mada)} disabled={selected ? 'disabled' : ''}>
        {selected ? Drupal.t('selected') : Drupal.t('select')}
      </button>
    </div>
  </div>
);

export default SavedCardItem;
