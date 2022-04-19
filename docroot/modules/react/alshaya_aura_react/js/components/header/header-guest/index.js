import React from 'react';
import Popup from 'reactjs-popup';
import HeaderLoyaltyCta from '../header-loyalty-cta';
import SignUpHeader from '../sign-up-header';
import SignUpCompleteHeader from '../signup-complete-header';
import Points from '../points';
import AuraCongratulationsModal from '../../../../../alshaya_spc/js/aura-loyalty/components/aura-congratulations';
import getStringMessage from '../../../../../js/utilities/strings';

class HeaderGuest extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isCongratulationsModalOpen: false,
      closeModalCalled: false,
    };
  }

  static getDerivedStateFromProps(props, state) {
    if (!state.closeModalCalled) {
      return { isCongratulationsModalOpen: props.showCongratulations };
    }
    return null;
  }

  closeCongratulationsModal = () => {
    this.setState({
      isCongratulationsModalOpen: false,
      closeModalCalled: true,
    });
  }

  render() {
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
    } = this.props;

    const {
      isCongratulationsModalOpen,
    } = this.state;

    if (isMobileTab) {
      if (signUpComplete !== true) {
        return null;
      }
      return <Points points={points} tier={tier} />;
    }

    if (signUpComplete) {
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
          <Popup
            className="aura-modal-congratulations"
            open={isCongratulationsModalOpen}
            closeOnEscape={false}
            closeOnDocumentClick={false}
          >
            <AuraCongratulationsModal
              closeCongratulationsModal={() => this.closeCongratulationsModal()}
              headerText={getStringMessage('join_aura_congratulations_header')}
              bodyText={getStringMessage('join_aura_congratulations_text')}
              downloadText={getStringMessage('join_aura_congratulations_download_text')}
            />
          </Popup>
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
          isNotExpandable={clickedNotYou ? false : isNotExpandable}
        />
      </div>
    );
  }
}

export default HeaderGuest;
