import React from 'react';
import {
  getStorageInfo,
} from '../../../../js/utilities/storage';
import { getAuraLocalStorageKey } from '../../utilities/aura_utils';
import {
  getUserDetails,
  getUserAuraStatus,
  getUserAuraTier,
  getAllAuraStatus,
} from '../../utilities/helper';
import Loading from '../../../../alshaya_spc/js/utilities/loading';
import {
  getCustomerDetails,
} from '../../utilities/header_helper';
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
      loyaltyStatus: getUserAuraStatus(),
      tier: getUserAuraTier(),
      points: 0,
      cardNumber: '',
    };

    if (!getUserDetails().id) {
      const localStorageValues = getStorageInfo(getAuraLocalStorageKey());
      if (localStorageValues) {
        this.state = {
          ...this.state,
          ...localStorageValues,
          signUpComplete: true,
        };
      }
    }
  }

  componentDidMount() {
    const {
      loyaltyStatus,
      tier,
    } = this.state;

    // Event listener to listen to customer data API call event.
    document.addEventListener('customerDetailsFetched', this.updateStates, false);

    // Event listener to listen to actions on loyalty blocks of my account and my aura page.
    document.addEventListener('loyaltyStatusUpdatedFromLoyaltyBlock', this.updateLoyaltyStatus, false);

    // Event listener to listen to actions on different sections of header
    // like shop tab or sign in/ register tab.
    document.addEventListener('loyaltyStatusUpdatedFromHeader', this.updateStates, false);

    // No API call to fetch points for anonymous users or user with
    // loyalty status APC_NOT_LINKED_NOT_U.
    if (!getUserDetails().id || loyaltyStatus === getAllAuraStatus().APC_NOT_LINKED_NOT_U) {
      this.setState({
        wait: false,
      });
      return;
    }

    // Get customer details.
    getCustomerDetails(tier, loyaltyStatus);
  }

  // Event listener callback to update header states.
  updateStates = (data) => {
    const { stateValues } = data.detail;
    this.setState({
      ...stateValues,
    });
  }

  updateLoyaltyStatus = (auraStatus) => {
    const stateValues = {
      loyaltyStatus: auraStatus.detail,
    };

    if (auraStatus.detail === getAllAuraStatus().APC_LINKED_NOT_VERIFIED) {
      stateValues.signUpComplete = true;
    }
    this.setState(stateValues);
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
        />
      );
    }
    // For guest users.
    return (
      <HeaderGuest
        points={points}
        cardNumber={cardNumber}
        isMobileTab={isMobileTab}
        isDesktop={isDesktop}
        isHeaderModalOpen={isHeaderModalOpen}
        openHeaderModal={this.openHeaderModal}
        isNotExpandable={isNotExpandable}
        signUpComplete={signUpComplete}
      />
    );
  }
}

export default Header;
