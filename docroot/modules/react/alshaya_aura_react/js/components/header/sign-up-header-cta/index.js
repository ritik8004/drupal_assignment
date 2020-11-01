import React from 'react';
import AuraHeaderIcon from '../../../svg-component/aura-header-icon';
import {
  getUserDetails,
} from '../../../utilities/helper';
import Points from '../points';

const getAuraLabel = (isDesktop, previewClass, openHeaderModal) => {
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

const getHeaderMarkup = (props) => {
  const {
    isNotExpandable,
    openHeaderModal,
    points,
    signUpComplete,
    isDesktop,
    isMobileTab,
    isHeaderModalOpen,
  } = props;

  const { baseUrl, pathPrefix } = drupalSettings.path;

  const { id: userId, userName } = getUserDetails();

  const previewClass = isHeaderModalOpen === true ? 'open' : '';

  // For logged in users.
  if (userId) {
    if (isMobileTab) {
      if (!signUpComplete) {
        return Drupal.t('my account');
      }
      return (
        <div className="aura-logged-in-rewards-header">
          <div className="account-name">
            <span className="name">{ userName }</span>
          </div>
          <div className="account-points">
            <Points points={points} />
          </div>
        </div>
      );
    }
    if (signUpComplete) {
      return (
        <div className={`aura-header-link ${previewClass}`}>
          <a
            className="user-points"
            href={`${baseUrl}${pathPrefix}user/${userId}/loyalty-club`}
          >
            <Points points={points} />
          </a>
        </div>
      );
    }

    return getAuraLabel(isDesktop, previewClass, openHeaderModal);
  }

  // For guest users.
  if (isMobileTab) {
    if (!signUpComplete) {
      return null;
    }
    return <Points points={points} />;
  }
  if (!isNotExpandable) {
    return getAuraLabel(isDesktop, previewClass, openHeaderModal);
  }

  return null;
};

const SignUpHeaderCta = (props) => {
  const headerMarkup = getHeaderMarkup(props);

  return (
    <>
      { headerMarkup }
    </>
  );
};

export default SignUpHeaderCta;
