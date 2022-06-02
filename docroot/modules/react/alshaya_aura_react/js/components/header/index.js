import React from 'react';
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

class Header extends React.Component {
  constructor(props) {
    super(props);
    const { isNotExpandable } = this.props;

    this.state = {
      wait: true,
      signUpComplete: false,
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
    const { stateValues, clickedNotYou } = data.detail;
    const states = { ...stateValues };

    if (clickedNotYou) {
      states.clickedNotYou = clickedNotYou;
    }

    if (stateValues.loyaltyStatus === getAllAuraStatus().APC_LINKED_NOT_VERIFIED) {
      states.signUpComplete = true;
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
      </>
    );
  }
}

export default Header;
