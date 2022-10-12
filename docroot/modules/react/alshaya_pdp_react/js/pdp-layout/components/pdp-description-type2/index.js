import React from 'react';
import parse from 'html-react-parser';
import PdpSectionText from '../utilities/pdp-section-text';

const PdpDescriptionType2 = (props) => {
  const {
    description,
    additionalAttributes,
  } = props;

  const descriptionData = { ...description, ...additionalAttributes };

  return Object.keys(descriptionData).map((item, index) => (
    <div key={item} className="magv2-new-desc__item alshaya-accordion--mobile">
      {(index === 0) ? (
        <>
          <h2 className="magv2-new-desc__item__title alshaya-accordion-header">{Drupal.t('DESCRIPTION')}</h2>
          <PdpSectionText className="magv2-new-desc__item__text alshaya-accordion-content">
            {parse(descriptionData[index].value['#markup'])}
          </PdpSectionText>
        </>
      )
        : (
          <>
            <h2 className="magv2-new-desc__item__title alshaya-accordion-header">{ Drupal.t('@label', { '@label': descriptionData[item].label })}</h2>
            <PdpSectionText className="magv2-new-desc__item__text alshaya-accordion-content">
              {parse(descriptionData[item].value)}
            </PdpSectionText>
          </>
        )}
    </div>
  ));
};

export default PdpDescriptionType2;
