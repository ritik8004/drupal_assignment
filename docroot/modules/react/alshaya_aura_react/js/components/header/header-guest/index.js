import React from 'react';
import {
  handleSignUp,
  handleNotYou,
} from '../../../utilities/header_helper';
import HeaderLoyaltyCta from '../header-loyalty-cta';
import SignUpHeader from '../sign-up-header';
import SignUpCompleteHeader from '../signup-complete-header';
import Points from '../points';

const HeaderGuest = (props) => {
  const {
    points,
    cardNumber,
    isMobileTab,
    signUpComplete,
    isDesktop,
    isHeaderModalOpen,
    openHeaderModal,
    isNotExpandable,
  } = props;

  if (isMobileTab === true) {
    if (signUpComplete !== true) {
      return null;
    }
    return <Points points={points} />;
  }

  if (signUpComplete === true) {
    return (
      <>
        <HeaderLoyaltyCta
          isDesktop={isDesktop}
          isHeaderModalOpen={!isDesktop && signUpComplete ? true : isHeaderModalOpen}
          openHeaderModal={openHeaderModal}
          isNotExpandable={!isDesktop && signUpComplete ? true : isNotExpandable}
        />
        <SignUpCompleteHeader
          handleNotYou={() => handleNotYou(cardNumber)}
          isHeaderModalOpen={!isDesktop && signUpComplete ? true : isHeaderModalOpen}
          cardNumber={cardNumber}
          noRegisterLinks
        />
      </>
    );
  }

  return (
    <>
      <HeaderLoyaltyCta
        isDesktop={isDesktop}
        isHeaderModalOpen={!isDesktop && signUpComplete ? true : isHeaderModalOpen}
        openHeaderModal={openHeaderModal}
        isNotExpandable={!isDesktop && signUpComplete ? true : isNotExpandable}
      />
      <SignUpHeader
        handleSignUp={handleSignUp}
        isHeaderModalOpen={!isDesktop && signUpComplete ? true : isHeaderModalOpen}
        openHeaderModal={openHeaderModal}
        isNotExpandable={isNotExpandable}
      />
    </>
  );
};

export default HeaderGuest;
