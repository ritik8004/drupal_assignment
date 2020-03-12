import React from 'react';
import SavedCardItem from './SavedCardItem';

const SavedCardsList = ({ closeStoreListModal, selected, onExistingCardSelect, onNewCardClick }) => {
  const cardITems = Object.entries(drupalSettings.checkoutCom.tokenizedCards).map(([key, card]) => (
    <SavedCardItem
      key={card.public_hash}
      cardInfo={card}
      selected={selected === key}
      onSelect={onExistingCardSelect}
    />
  ));

  return (
    <>
      <header className="spc-change-address">{Drupal.t('change payment card')}</header>
      <a className="close" onClick={() => closeStoreListModal()}>&times;</a>
      <div className="address-list-content">
        <div className="spc-add-new-address-btn" onClick={onNewCardClick}>
          {Drupal.t('add new card')}
        </div>
        <div className="spc-checkout-address-list">{cardITems}</div>
      </div>
    </>
  );
};

export default SavedCardsList;
