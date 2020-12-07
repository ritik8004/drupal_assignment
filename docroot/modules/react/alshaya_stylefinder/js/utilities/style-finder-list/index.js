import React from 'react';

const StyleFinderList = ({ className = '', stylefinderListImgSrc = '' }) => (
  <ul className={`style-finder-list ${className}`}>
    <li className="style-finder-list-item">
      <div className="style-finder-list-image">
        <img src={stylefinderListImgSrc} />
      </div>
      <div className="style-finder-list-title">
        {Drupal.t('Unlined')}
      </div>
      <div className="style-finder-list-text">
        {Drupal.t('Nothing comes between you and your bra, but you\'ll still feel supported.')}
      </div>
    </li>
    <li className="style-finder-list-item">
      <div className="style-finder-list-image">
        <img src={stylefinderListImgSrc} />
      </div>
      <div className="style-finder-list-title">
        {Drupal.t('Lightly Lined')}
      </div>
      <div className="style-finder-list-text">
        {Drupal.t('Just a little something for smooth shape, no show-through and plenty of support.')}
      </div>
    </li>
    <li className="style-finder-list-item">
      <div className="style-finder-list-image">
        <img src={stylefinderListImgSrc} />
      </div>
      <div className="style-finder-list-title">
        {Drupal.t('Push-Up')}
      </div>
      <div className="style-finder-list-text">
        {Drupal.t('Comfortable, state-of-the-art padding for extra support and a subtle to sultry lift.')}
      </div>
    </li>
  </ul>
);

export default StyleFinderList;
