import React from 'react';
import PdpSectionTitle from '../utilities/pdp-section-title';
import PdpSectionText from '../utilities/pdp-section-text';

const PdpStandardDelivery = () => (
  <div className="magv2-pdp-standard-delivery-wrapper card">
    <div className="magv2-standard-delivery-title-wrapper">
      <PdpSectionTitle>
        {Drupal.t('standard delivery')}
        <span className="standard-delivery-title-tag free-tag">{Drupal.t('free')}</span>
      </PdpSectionTitle>
    </div>
    <PdpSectionText className="standard-delivery-detail">
      <span>{Drupal.t('free delivery for orders over KWD 9, to all areas across Kuwait.')}</span>
    </PdpSectionText>
  </div>
);

export default PdpStandardDelivery;
