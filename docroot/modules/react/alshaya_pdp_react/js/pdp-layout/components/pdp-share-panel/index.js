import React from 'react';
import parse from 'html-react-parser';
import { CopyToClipboard } from 'react-copy-to-clipboard';

const PdpSharePanel = () => {
  const shareUrl = window.location.href;
  const { sharethis } = drupalSettings;

  return (
    <>
      <div className="pdp-share-panel">
        <div className="sharethis-wrapper">
          {parse(sharethis.content)}
        </div>
        <CopyToClipboard text={shareUrl}>
          <button type="button">{Drupal.t('copy page link')}</button>
        </CopyToClipboard>
      </div>
    </>
  );
};

export default PdpSharePanel;
