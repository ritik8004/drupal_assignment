import React from 'react';
import parse from 'html-react-parser';
import CopyPageLink from './copy-page-link';
import PdpSectionTitle from '../utilities/pdp-section-title';

const PdpSharePanel = () => {
  const { sharethis } = drupalSettings;

  const showSharePanel = () => {
    document.querySelector('.magv2-pdp-share-wrapper').classList.toggle('show-share-panel');
  };

  return (
    <div className="magv2-pdp-share-wrapper card">
      <div className="magv2-share-title-wrapper">
        <PdpSectionTitle>
          {Drupal.t('Share this page')}
        </PdpSectionTitle>
        <div className="magv2-accordion" onClick={() => showSharePanel()} />
      </div>
      <div className="pdp-share-panel">
        <div className="sharethis-wrapper">
          {parse(sharethis.content)}
        </div>
        <CopyPageLink />
      </div>
    </div>
  );
};

export default PdpSharePanel;
