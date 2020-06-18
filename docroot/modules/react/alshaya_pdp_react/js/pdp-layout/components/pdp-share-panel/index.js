import React from 'react';
import parse from 'html-react-parser';
import CopyPageLink from './copy-page-link';

const PdpSharePanel = () => {
  const { sharethis } = drupalSettings;

  return (
    <>
      <div className="pdp-share-panel">
        <div className="sharethis-wrapper">
          {parse(sharethis.content)}
        </div>
        <CopyPageLink />
      </div>
    </>
  );
};

export default PdpSharePanel;
