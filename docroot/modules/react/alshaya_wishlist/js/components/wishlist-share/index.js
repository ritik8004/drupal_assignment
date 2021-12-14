import React from 'react';
import ShareIcon from './share-icon';

const WishlistShare = () => (
  <>
    <span className="text">{Drupal.t('Share')}</span>
    <span className="icon"><ShareIcon /></span>
  </>
);

export default WishlistShare;
