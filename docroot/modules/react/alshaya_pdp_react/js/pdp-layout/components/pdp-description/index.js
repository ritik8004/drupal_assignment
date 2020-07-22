import React from 'react';
import parse from 'html-react-parser';
import PdpSectionTitle from '../utilities/pdp-section-title';
import PdpSectionText from '../utilities/pdp-section-text';
import DescriptionContent from '../pdp-desc-popup-content';

const PpdDescription = (props) => {
  const openModal = () => {
    document.querySelector('body').classList.add('desc-overlay');
  };

  const closeModal = () => {
    document.querySelector('body').classList.remove('desc-overlay');
  };

  const {
    pdpShortDesc, pdpDescription, skuCode, finalPrice, pdpProductPrice, title,
  } = props;

  return (
    <div className="magv2-pdp-description-wrapper card fadeInUp" style={{ animationDelay: '0.8s' }}>
      <PdpSectionTitle>{Drupal.t('product details')}</PdpSectionTitle>
      <PdpSectionText className="short-desc"><p>{parse(pdpShortDesc)}</p></PdpSectionText>
      <div className="magv2-desc-readmore-link" onClick={() => openModal()}>
        {Drupal.t('Read more')}
      </div>
      <DescriptionContent
        closeModal={closeModal}
        title={title}
        pdpProductPrice={pdpProductPrice}
        finalPrice={finalPrice}
        skuCode={skuCode}
        pdpDescription={pdpDescription}
      />
    </div>
  );
};

export default PpdDescription;
