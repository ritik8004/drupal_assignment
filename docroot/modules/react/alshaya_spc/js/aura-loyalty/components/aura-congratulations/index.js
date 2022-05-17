import React from 'react';
import SectionTitle from '../../../utilities/section-title';
import AuraAppLinks from '../utilities/aura-app-links';

const AuraCongratulationsModal = (props) => {
  const {
    closeCongratulationsModal,
    headerText,
    bodyText,
    downloadText,
  } = props;
  return (
    <div className="aura-congratulations-modal">
      <div className="aura-modal-header">
        <SectionTitle>{headerText}</SectionTitle>
        <button type="button" className="close" onClick={() => closeCongratulationsModal()} />
      </div>
      <div className="aura-modal-body">
        <div className="congratulations-text">{bodyText}</div>
        <div className="download-text">{downloadText}</div>
        <div className="mobile-only"><AuraAppLinks /></div>
      </div>
    </div>
  );
};

export default AuraCongratulationsModal;
