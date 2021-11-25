import React from 'react';
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
    clickedNotYou,
    notYouFailed,
    tier,
  } = props;

  if (isMobileTab === true) {
    if (signUpComplete !== true) {
      return null;
    }
    return <Points points={points} tier={tier} />;
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
          isHeaderModalOpen={!isDesktop && signUpComplete ? true : isHeaderModalOpen}
          cardNumber={cardNumber}
          noRegisterLinks={!isDesktop}
          notYouFailed={notYouFailed}
        />
      </>
    );
  }

  return (
    <div className="aura-header-guest-tooltip">
      <HeaderLoyaltyCta
        isDesktop={isDesktop}
        isHeaderModalOpen={!isDesktop && signUpComplete ? true : isHeaderModalOpen}
        openHeaderModal={openHeaderModal}
        isNotExpandable={!isDesktop && signUpComplete ? true : isNotExpandable}
      />
      <SignUpHeader
        isHeaderModalOpen={!isDesktop && signUpComplete ? true : isHeaderModalOpen}
        openHeaderModal={openHeaderModal}
        isNotExpandable={clickedNotYou === true ? false : isNotExpandable}
      />
    </div>
  );
};

export default HeaderGuest;
