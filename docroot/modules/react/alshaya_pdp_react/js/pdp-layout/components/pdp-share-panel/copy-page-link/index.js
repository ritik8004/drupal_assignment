import React from 'react';

const copyToClipboard = () => {
  const textarea = document.createElement('textarea');
  textarea.innerHTML = window.location.href;
  document.body.appendChild(textarea);
  textarea.select();
  document.execCommand('copy');
  textarea.remove();
};

const CopyPageLink = () => (
  <button onClick={() => copyToClipboard()} type="button">
    {Drupal.t('Copy page link')}
  </button>
);

export default CopyPageLink;
