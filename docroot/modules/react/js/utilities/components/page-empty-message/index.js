import React from 'react';

const PageEmptyMessage = (message, replacements, context) => (
  <div className="empty-message">
    <div className="text">{Drupal.t(message, replacements, { context })}</div>
    <div className="actions">
      <a href={Drupal.url('')}>{Drupal.t('go shopping', {}, { context })}</a>
    </div>
  </div>
);

export default PageEmptyMessage;
