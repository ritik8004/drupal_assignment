import React from 'react';
import SignUpHeader from './sign-up-header';
import {
  setStorageInfo,
  getStorageInfo,
  removeStorageInfo,
} from '../../../../js/utilities/storage';
import { getAuraLocalStorageKey } from '../../utilities/aura_utils';
import SignUpCompleteHeader from './signup-complete-header';
import {
  getUserDetails,
  getUserAuraStatus,
  getUserAuraTier,
  getAllAuraStatus,
} from '../../utilities/helper';
import { getAPIData } from '../../utilities/api/fetchApiData';
import SignUpHeaderCta from './sign-up-header-cta';
import Loading from '../../../../alshaya_spc/js/utilities/loading';

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
      points,
    } = this.state;

    // No API call to fetch points for anonymous users or user with
    // loyalty status APC_NOT_LINKED_NOT_U.
    if (!getUserDetails().id || loyaltyStatus === getAllAuraStatus().APC_NOT_LINKED_NOT_U) {
      this.setState({
        wait: false,
      });
      return;
    }

    // API call to get customer points for logged in users.
    const apiUrl = `get/loyalty-club/get-customer-details?tier=${tier}&status=${loyaltyStatus}`;
    const apiData = getAPIData(apiUrl);

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.data !== undefined && result.data.error === undefined) {
          const userLoyaltyStatus = result.data.auraStatus !== undefined
            ? result.data.auraStatus : loyaltyStatus;

          this.setState({
            loyaltyStatus: userLoyaltyStatus,
            tier: result.data.tier || tier,
            points: result.data.auraPoints || points,
          });

          // If user's loyalty status is APC_LINKED_VERIFIED or APC_LINKED_NOT_VERIFIED,
          // then sign up is complete for the user and we show points in header.
          if (userLoyaltyStatus === getAllAuraStatus().APC_LINKED_VERIFIED
            || userLoyaltyStatus === getAllAuraStatus().APC_LINKED_NOT_VERIFIED) {
            this.setState({
              signUpComplete: true,
            });
          }
        }
        this.setState({
          wait: false,
        });
      });
    }
  }

  openHeaderModal = () => {
    const { isNotExpandable } = this.props;
    if (!isNotExpandable) {
      this.setState((prevState) => ({
        isHeaderModalOpen: !prevState.isHeaderModalOpen,
      }));
    }
  };

  handleSignUp = (auraUserDetails) => {
    this.setState({
      signUpComplete: true,
    });

    // For anonymous users, store aura data in local storage and update state.
    if (!getUserDetails().id && auraUserDetails) {
      setStorageInfo(auraUserDetails.data, getAuraLocalStorageKey());
      this.setState({
        ...auraUserDetails.data,
      });
    }
  };

  handleNotYou = () => {
    removeStorageInfo(getAuraLocalStorageKey());
    this.setState({
      signUpComplete: false,
    });
  }

  render() {
    const {
      wait,
      signUpComplete,
      isHeaderModalOpen,
      apc_identifier_number: cardNumber,
      points,
      loyaltyStatus,
    } = this.state;

    const { isNotExpandable } = this.props;

    const { id: userId } = getUserDetails();

    if (wait) {
      return (
        <div className="aura-header-waiting-wrapper">
          <Loading />
        </div>
      );
    }

    let headerPopUp = null;

    if (userId) {
      if (loyaltyStatus === getAllAuraStatus().APC_NOT_LINKED_DATA) {
        headerPopUp = (
          <SignUpCompleteHeader
            handleNotYou={this.handleNotYou}
            isHeaderModalOpen={isHeaderModalOpen}
            cardNumber={cardNumber}
            isNotExpandable={isNotExpandable}
          />
        );
      } else if (loyaltyStatus === getAllAuraStatus().APC_NOT_LINKED_NO_DATA
        || loyaltyStatus === getAllAuraStatus().APC_NOT_LINKED_NOT_U) {
        headerPopUp = (
          <SignUpHeader
            handleSignUp={this.handleSignUp}
            isHeaderModalOpen={isHeaderModalOpen}
            openHeaderModal={this.openHeaderModal}
          />
        );
      }
    } else if (signUpComplete) {
      headerPopUp = (
        <SignUpCompleteHeader
          handleNotYou={this.handleNotYou}
          isHeaderModalOpen={isHeaderModalOpen}
          cardNumber={cardNumber}
          isNotExpandable={isNotExpandable}
        />
      );
    } else {
      headerPopUp = (
        <SignUpHeader
          handleSignUp={this.handleSignUp}
          isHeaderModalOpen={isHeaderModalOpen}
          openHeaderModal={this.openHeaderModal}
        />
      );
    }

    return (
      <>
        <SignUpHeaderCta
          isNotExpandable={isNotExpandable}
          openHeaderModal={this.openHeaderModal}
          points={points}
          signUpComplete={signUpComplete}
        />
        { headerPopUp }
      </>
    );
  }
}

export default Header;
