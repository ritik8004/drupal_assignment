import React from 'react';

const ReturnCollectionDetails = () => (
  <>
    <div className="return-collection-wrapper">
      <div className="return-collection-title">
        { Drupal.t('Return Collection Details') }
      </div>
      <div className="return-collection-message">
        { Drupal.t('Your chosen return items will be picked up in 3-4 days.') }
      </div>
    </div>
  </>
);

export default ReturnCollectionDetails;
