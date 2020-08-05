import React from 'react';

const copyToClipboard = (e) => {
  const textarea = document.createElement('textarea');
  textarea.innerHTML = window.location.href;
  document.body.appendChild(textarea);
  textarea.select();
  document.execCommand('copy');
  textarea.remove();
  // Change text briefly so user knows.
  e.currentTarget.innerHTML = Drupal.t('Link Copied!');
  e.currentTarget.classList.add('copied');
  // Revert back after 2s.
  setTimeout(() => {
    const copyButton = document.querySelector('.copy-button');
    copyButton.innerHTML = Drupal.t('Copy page link');
    copyButton.classList.remove('copied');
  }, 2000);
};

const CopyPageLink = () => (
  <button className="copy-button" onClick={(e) => copyToClipboard(e)} type="button">
    {Drupal.t('Copy page link')}
  </button>
);

export default CopyPageLink;
