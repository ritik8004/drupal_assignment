import React from 'react';
import parse from 'html-react-parser';
import PdpSectionText from '../utilities/pdp-section-text';

const PdpNewDescContainer = (props) => {
  const {
    description,
    additionalAttributes,
  } = props;

  const descriptionData = { ...description, ...additionalAttributes };

  return Object.keys(descriptionData).map((item, index) => (
    <div key={item}>
      {(index === 0) ? (
        <>
          <h2>{Drupal.t('DESCRIPTION')}</h2>
          <PdpSectionText>
            {parse(descriptionData[index].value['#markup'])}
          </PdpSectionText>
        </>
      )
        : (
          <>
            <h2>{ Drupal.t('@label', { '@label': descriptionData[item].label })}</h2>
            <PdpSectionText>
              {parse(descriptionData[item].value)}
            </PdpSectionText>
          </>
        )}
    </div>
  ));
};

export default PdpNewDescContainer;
