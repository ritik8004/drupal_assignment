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
  } = props;

  const { baseUrl, pathPrefix } = drupalSettings.path;

  const { id: userId } = getUserDetails();

  let headerMarkup = null;

  if (userId) {
    if (signUpComplete) {
      headerMarkup = (
        <div className="aura-header-link">
          <a
            className="user-points"
            href={`${baseUrl}${pathPrefix}user/${userId}/loyalty-club`}
          >
            <span className="points">{`${points} ${Drupal.t('Pts')}`}</span>
          </a>
        </div>
      );
    } else {
      headerMarkup = (
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
  } else if (!isNotExpandable) {
    headerMarkup = (
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

  return (
    <>
      { headerMarkup }
    </>
  );
};

export default SignUpHeaderCta;
