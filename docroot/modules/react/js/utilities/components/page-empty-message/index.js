import React from 'react';

const PageEmptyMessage = (message, linkText) => (
  <div className="empty-message">
    <div className="text">{message}</div>
    <div className="actions">
      <a href={Drupal.url('')}>{linkText}</a>
    </div>
  </div>
);

export default PageEmptyMessage;
