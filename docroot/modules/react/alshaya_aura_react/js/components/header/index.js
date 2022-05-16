import React from 'react';
import Popup from 'reactjs-popup';
import { getAuraLocalStorageKey, getAuraDetailsDefaultState } from '../../utilities/aura_utils';
import {
  getUserDetails,
  getAllAuraStatus,
} from '../../utilities/helper';
import Loading from '../../../../alshaya_spc/js/utilities/loading';
import {
  getCustomerDetails,
} from '../../utilities/customer_helper';
import HeaderLoggedIn from './header-loggedIn';
import HeaderGuest from './header-guest';
import AuraCongratulationsModal from '../../../../alshaya_spc/js/aura-loyalty/components/aura-congratulations';
import getStringMessage from '../../../../js/utilities/strings';

// Define a global variable to to identify if congratulation popup is already
// open or not. If already open, we will change this variable value to avoid
// having two popups display at the same time.
window.isCongratulationPopupOpen = false;

class Header extends React.Component {
  constructor(props) {
    super(props);
    const { isNotExpandable } = this.props;

    this.state = {
      wait: true,
      signUpComplete: false,
      showCongratulations: false,
      isHeaderModalOpen: !!isNotExpandable,
      ...getAuraDetailsDefaultState(),
    };

    const localStorageValues = Drupal.getItemFromLocalStorage(getAuraLocalStorageKey());

    if (localStorageValues) {
      // Use localStorage values only for anonymous users.
      if (!getUserDetails().id) {
        this.state = {
          ...this.state,
          ...localStorageValues,
          signUpComplete: true,
        };
        return;
      }
      // Remove localstorage values if its a logged in user.
      Drupal.removeItemFromLocalStorage(getAuraLocalStorageKey());
    }
  }

  componentDidMount() {
    const {
      loyaltyStatus,
    } = this.state;

    // Event listener to listen to customer data API call event.
    document.addEventListener('customerDetailsFetched', this.updateState, false);

    // Event listener to listen to actions on loyalty blocks.
    document.addEventListener('loyaltyStatusUpdated', this.updateState, false);

    // No API call to fetch points for anonymous users or user with
    // loyalty status APC_NOT_LINKED_NOT_U.
    if (!getUserDetails().id || loyaltyStatus === getAllAuraStatus().APC_NOT_LINKED_NOT_U) {
      this.setState({
        wait: false,
      });
      return;
    }

    // Get customer details.
    getCustomerDetails();
  }

  // Event listener callback to update header states.
  updateState = (data) => {
    const { stateValues, clickedNotYou, showCongratulationsPopup } = data.detail;
    const states = { ...stateValues };

    if (clickedNotYou) {
      states.clickedNotYou = clickedNotYou;
    }

    if (stateValues.loyaltyStatus === getAllAuraStatus().APC_LINKED_NOT_VERIFIED) {
      states.signUpComplete = true;
    }

    // Show congratulations popup only if showCongratulationsPopup is defined and true.
    if ((typeof showCongratulationsPopup !== 'undefined')
      && showCongratulationsPopup
    ) {
      states.showCongratulations = true;
    }

    this.setState({
      ...states,
    });
  }

  openHeaderModal = () => {
    const { isNotExpandable } = this.props;
    if (!isNotExpandable) {
      this.setState((prevState) => ({
        isHeaderModalOpen: !prevState.isHeaderModalOpen,
      }));
    }
  };

  closeCongratulationsModal = () => {
    // Set the congratulation popup global variable flag to false.
    window.isCongratulationPopupOpen = false;
    // We need this code to close the mobile menu that opens up when
    // congratulations popup display. To close the menu, we are triggeing
    // mobile menu close button.
    document
      .querySelector('.mobile--close')
      .dispatchEvent(
        new CustomEvent('click'),
      );

    this.setState({
      showCongratulations: false,
    });
  };

  getCongratulationsPopup() {
    const { showCongratulations } = this.state;

    // If congratulation popup is already open, we will return null.
    if (window.isCongratulationPopupOpen) {
      return null;
    }

    // If congratulation popup is not yet open and show popup flag is true, we
    // will set global variable isCongratulationPopupOpen value to true to avoid
    // having two popups open at the same time.
    if (showCongratulations && !window.isCongratulationPopupOpen) {
      window.isCongratulationPopupOpen = true;

      // We need this code to close the mobile menu that opens up when
      // congratulations popup display. To close the menu, we are triggeing
      // mobile menu close button.
      document
        .querySelector('.mobile--close')
        .dispatchEvent(
          new CustomEvent('click'),
        );
    }

    return (
      <Popup
        className="aura-modal-congratulations"
        open={showCongratulations}
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
    );
  }

  render() {
    const {
      wait,
      signUpComplete,
      isHeaderModalOpen,
      cardNumber,
      points,
      loyaltyStatus,
      clickedNotYou,
      notYouFailed,
      tier,
      firstName,
      lastName,
    } = this.state;

    const {
      isNotExpandable,
      isDesktop,
      isMobileTab,
      isHeaderShop,
    } = this.props;

    const { id: userId } = getUserDetails();

    if (wait) {
      return (
        <div className="aura-header-waiting-wrapper">
          <Loading />
        </div>
      );
    }

    // For logged in users.
    if (userId) {
      return (
        <>
          <HeaderLoggedIn
            loyaltyStatus={loyaltyStatus}
            points={points}
            cardNumber={cardNumber}
            isMobileTab={isMobileTab}
            isDesktop={isDesktop}
            isHeaderModalOpen={isHeaderModalOpen}
            openHeaderModal={this.openHeaderModal}
            isNotExpandable={isNotExpandable}
            isHeaderShop={isHeaderShop}
            signUpComplete={signUpComplete}
            notYouFailed={notYouFailed}
            tier={tier}
            firstName={firstName}
            lastName={lastName}
          />
          {this.getCongratulationsPopup()}
        </>
      );
    }
    // For guest users.
    return (
      <>
        <HeaderGuest
          points={points}
          cardNumber={cardNumber}
          isMobileTab={isMobileTab}
          isDesktop={isDesktop}
          isHeaderModalOpen={isHeaderModalOpen}
          openHeaderModal={this.openHeaderModal}
          isNotExpandable={isNotExpandable}
          signUpComplete={signUpComplete}
          clickedNotYou={clickedNotYou}
          notYouFailed={notYouFailed}
          tier={tier}
        />
        {this.getCongratulationsPopup()}
      </>
    );
  }
}

export default Header;
