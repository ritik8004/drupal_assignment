import React from 'react';
import AuraHeaderIcon from '../../../svg-component/aura-header-icon';

const HeaderLoyaltyCta = (props) => {
  const {
    isDesktop,
    isHeaderModalOpen,
    openHeaderModal,
    isNotExpandable,
  } = props;

  const previewClass = isHeaderModalOpen ? 'open' : '';

  if (isDesktop) {
    return (
      <div className={`aura-header-link ${previewClass}`}>
        <a
          className="join-aura"
          onClick={() => openHeaderModal()}
        >
          <AuraHeaderIcon />
        </a>
      </div>
    );
  }

  if (isNotExpandable) {
    return null;
  }

  return (
    <div className={`aura-header-link ${previewClass}`}>
      <div className="aura-header-hb-menu-title">
        <span className="join-aura"><AuraHeaderIcon /></span>
        <span
          className="aura-header-hb-menu-expand"
          onClick={() => openHeaderModal()}
        />
      </div>
    </div>
  );
};

export default HeaderLoyaltyCta;
