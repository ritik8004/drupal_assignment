import React from 'react';
import parse from 'html-react-parser';
import PdpSectionTitle from '../utilities/pdp-section-title';
import PdpSectionText from '../utilities/pdp-section-text';
import DescriptionContent from '../pdp-desc-popup-content';
import ProductDetailSVG from '../../../svg-component/product-detail-svg';

const PpdDescription = (props) => {
  const {
    pdpShortDesc, pdpDescription, skuCode, finalPrice,
    pdpProductPrice, title, getPanelData, removePanelData,
  } = props;

  const closeModal = () => {
    document.querySelector('body').classList.remove('overlay-desc');
    setTimeout(() => {
      removePanelData();
    }, 400);
  };

  const openModal = () => {
    // to make sure that markup is present in DOM.
    setTimeout(() => {
      document.querySelector('body').classList.add('overlay-desc');
    }, 150);

    return (
      <DescriptionContent
        closeModal={closeModal}
        title={title}
        pdpProductPrice={pdpProductPrice}
        finalPrice={finalPrice}
        skuCode={skuCode}
        pdpDescription={pdpDescription}
        overlayClass="overlay-desc"
      />
    );
  };

  return (
    <div className="magv2-pdp-description-wrapper card fadeInUp" style={{ animationDelay: '0.8s' }}>
      <PdpSectionTitle>
        <span className="magv2-card-icon-svg">
          <ProductDetailSVG />
        </span>
        {Drupal.t('product details')}
      </PdpSectionTitle>
      <PdpSectionText className="short-desc">{parse(pdpShortDesc)}</PdpSectionText>
      <div className="magv2-desc-readmore-link" onClick={() => getPanelData(openModal())}>
        {Drupal.t('Read more')}
      </div>
    </div>
  );
};

export default PpdDescription;
