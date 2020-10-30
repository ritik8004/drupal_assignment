import React from 'react';
import AuraHeaderIcon from '../../../svg-component/aura-header-icon';
import {
  getUserDetails,
} from '../../../utilities/helper';

const SignUpHeaderCta = (props) => {
  const {
    isNotExpandable,
    openHeaderModal,
    points,
    signUpComplete,
    loggedInMobile,
  } = props;

  const { baseUrl, pathPrefix } = drupalSettings.path;

  const { id: userId, userName } = getUserDetails();

  const getHeaderMarkup = () => {
    // For logged in users.
    if (userId) {
      if (loggedInMobile) {
        return (
          <div className="aura-logged-in-rewards-header">
            <div className="account-name">
              <span className="name">{ userName }</span>
            </div>
            <div className="account-points">
              <span className="points">{`${points} ${Drupal.t('Pts')}`}</span>
            </div>
          </div>
        );
      }
      if (signUpComplete) {
        return (
          <div className="aura-header-link">
            <a
              className="user-points"
              href={`${baseUrl}${pathPrefix}user/${userId}/loyalty-club`}
            >
              <span className="points">{`${points} ${Drupal.t('Pts')}`}</span>
            </a>
          </div>
        );
      }

      return (
        <div className="aura-header-link">
          <a
            className="join-aura"
            onClick={openHeaderModal}
          >
            <AuraHeaderIcon />
          </a>
        </div>
      );
    }

    // For guest users.
    if (!isNotExpandable) {
      return (
        <div className="aura-header-link">
          <a
            className="join-aura"
            onClick={openHeaderModal}
          >
            <AuraHeaderIcon />
          </a>
        </div>
      );
    }

    return null;
  };

  const headerMarkup = getHeaderMarkup();

  return (
    <>
      { headerMarkup }
    </>
  );
};

export default SignUpHeaderCta;
