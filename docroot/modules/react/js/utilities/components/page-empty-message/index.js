import React from 'react';

const PageEmptyMessage = (message, context) => (
  <div className="empty-message">
    <div className="text">{message}</div>
    <div className="actions">
      <a href={Drupal.url('')}>{Drupal.t('go shopping', {}, { context })}</a>
    </div>
  </div>
);

export default PageEmptyMessage;
