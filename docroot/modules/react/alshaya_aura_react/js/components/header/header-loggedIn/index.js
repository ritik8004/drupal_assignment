import React from 'react';
import {
  getAllAuraStatus,
} from '../../../utilities/helper';
import UserNamePoints from '../user-name-points';
import {
  handleSignUp,
  handleNotYou,
} from '../../../utilities/header_helper';
import HeaderLoyaltyCta from '../header-loyalty-cta';
import SignUpHeader from '../sign-up-header';
import LoggedInLinked from '../loggedIn-linked';
import SignUpCompleteHeader from '../signup-complete-header';

const HeaderLoggedIn = (props) => {
  const {
    loyaltyStatus,
    points,
    cardNumber,
    isMobileTab,
    isDesktop,
    isHeaderModalOpen,
    openHeaderModal,
    isNotExpandable,
    isHeaderShop,
    signUpComplete,
  } = props;

  if (loyaltyStatus === getAllAuraStatus().APC_NOT_LINKED_NO_DATA
    || loyaltyStatus === getAllAuraStatus().APC_NOT_LINKED_NOT_U) {
    if (isMobileTab) {
      return Drupal.t('my account');
    }

    return (
      <div className="aura-header-guest-tooltip">
        <HeaderLoyaltyCta
          isDesktop={isDesktop}
          isHeaderModalOpen={isHeaderModalOpen}
          openHeaderModal={openHeaderModal}
        />
        <SignUpHeader
          handleSignUp={handleSignUp}
          isHeaderModalOpen={isHeaderModalOpen}
          openHeaderModal={openHeaderModal}
        />
      </div>
    );
  } if (loyaltyStatus === getAllAuraStatus().APC_LINKED_NOT_VERIFIED
    || loyaltyStatus === getAllAuraStatus().APC_LINKED_VERIFIED) {
    if (isHeaderShop === true) {
      return null;
    }

    if (isMobileTab === true) {
      // Remove the username already present in the menu because username will be
      // redundant after this username component is rendered.
      if (document.querySelector('.aura-enabled #block-alshayamyaccountlinks-2 > .my-account-title')) {
        document.querySelector('.aura-enabled #block-alshayamyaccountlinks-2 > .my-account-title').remove();
      }
      return <UserNamePoints points={points} />;
    }

    return (
      <>
        <LoggedInLinked
          isDesktop={isDesktop}
          isHeaderModalOpen={isHeaderModalOpen}
          points={points}
        />
      </>
    );
  } if (loyaltyStatus === getAllAuraStatus().APC_NOT_LINKED_DATA) {
    if (isMobileTab === true) {
      return Drupal.t('my account');
    }

    return (
      <>
        <HeaderLoyaltyCta
          isDesktop={isDesktop}
          isHeaderModalOpen={!isDesktop && signUpComplete ? true : isHeaderModalOpen}
          openHeaderModal={openHeaderModal}
          isNotExpandable={!isDesktop && signUpComplete ? true : isNotExpandable}
        />
        <SignUpCompleteHeader
          isHeaderModalOpen={!isDesktop && signUpComplete ? true : isHeaderModalOpen}
          handleNotYou={() => handleNotYou(cardNumber)}
          cardNumber={cardNumber}
          noRegisterLinks
        />
      </>
    );
  }

  return null;
};

export default HeaderLoggedIn;
