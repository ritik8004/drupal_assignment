import React from 'react';
import AuraHeaderIcon from '../../../svg-component/aura-header-icon';
import {
  getUserDetails,
} from '../../../utilities/helper';

const getAuraLabel = (isDesktop, previewClass, openHeaderModal) => {
  console.log(isDesktop);
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

  return (
    <div className={`aura-header-link ${previewClass}`}>
      <div className="aura-header-hb-menu-title">
        <span className="preview-text">{Drupal.t('Say hello to')}</span>
        <span className="join-aura"><AuraHeaderIcon /></span>
        <span
          className="aura-header-hb-menu-expand"
          onClick={() => openHeaderModal()}
        />
      </div>
    </div>
  );
};

const SignUpHeaderCta = (props) => {
  const {
    isNotExpandable,
    openHeaderModal,
    points,
    signUpComplete,
    isHeaderModalOpen,
    isDesktop,
  } = props;

  const { baseUrl, pathPrefix } = drupalSettings.path;

  const { id: userId } = getUserDetails();

  let headerMarkup = null;
  const previewClass = isHeaderModalOpen === true ? 'open' : '';

  if (userId) {
    if (signUpComplete) {
      headerMarkup = (
        <div className={`aura-header-link ${previewClass}`}>
          <a
            className="user-points"
            href={`${baseUrl}${pathPrefix}user/${userId}/loyalty-club`}
          >
            <span className="points">{`${points} ${Drupal.t('Pts')}`}</span>
          </a>
        </div>
      );
    } else {
      headerMarkup = getAuraLabel(isDesktop, previewClass, openHeaderModal);
    }
  } else if (!isNotExpandable) {
    headerMarkup = getAuraLabel(isDesktop, previewClass, openHeaderModal);
  }

  return (
    <>
      { headerMarkup }
    </>
  );
};

export default SignUpHeaderCta;
