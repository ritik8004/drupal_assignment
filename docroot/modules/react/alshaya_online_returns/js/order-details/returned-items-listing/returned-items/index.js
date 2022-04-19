import React from 'react';

const ReturnedItems = ({
  subTitle,
}) => (
  <div className="returned-items">
    <div className="title-wrapper">
      <span>
        {Drupal.t('Returned Items', {}, { context: 'online_returns' })}
      </span>
      <span>
        {subTitle}
      </span>
    </div>
    <div className="items-list">
    </div>
  </div>
);

export default ReturnedItems;
