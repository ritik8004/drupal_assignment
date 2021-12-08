import React from 'react';

const PageEmptyMessage = (message) => (
  <div className="empty-message">
    <div className="text">{message}</div>
    <div className="actions">
      <a href={Drupal.url('')}>{Drupal.t('Continue shopping')}</a>
    </div>
  </div>
);

export default PageEmptyMessage;
