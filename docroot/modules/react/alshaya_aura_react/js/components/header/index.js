import React from 'react';
import SignUpHeader from './sign-up-header';
import AuraHeaderIcon from '../../svg-component/aura-header-icon';
import {
  setStorageInfo,
  getStorageInfo,
  removeStorageInfo,
} from '../../../../js/utilities/storage';
import { getAuraLocalStorageKey } from '../../utilities/aura_utils';
import SignUpCompleteHeader from './signup-complete-header';

class Header extends React.Component {
  constructor(props) {
    super(props);
    const localStorageValues = getStorageInfo(getAuraLocalStorageKey());
    if (localStorageValues) {
      this.state = {
        ...localStorageValues,
        signUpComplete: true,
      };
    } else {
      this.state = {
        signUpComplete: false,
      };
    }
  }

  openHeaderModal = () => {
    this.setState((prevState) => ({
      isHeaderModalOpen: !prevState.isHeaderModalOpen,
    }));
  };

  handleSignUp = (auraUserDetails) => {
    if (auraUserDetails) {
      setStorageInfo(auraUserDetails.data, getAuraLocalStorageKey());
      this.setState({
        ...auraUserDetails.data,
        signUpComplete: true,
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
      signUpComplete,
      isHeaderModalOpen,
      apc_identifier_number: cardNumber,
    } = this.state;

    return (
      <>
        <div className="aura-header-link">
          <a
            className="join-aura"
            onClick={() => this.openHeaderModal()}
          >
            <AuraHeaderIcon />
          </a>
        </div>
        {
          signUpComplete
            ? (
              <SignUpCompleteHeader
                handleNotYou={this.handleNotYou}
                isHeaderModalOpen={isHeaderModalOpen}
                cardNumber={cardNumber}
              />
            )
            : (
              <SignUpHeader
                handleSignUp={this.handleSignUp}
                isHeaderModalOpen={isHeaderModalOpen}
                openHeaderModal={this.openHeaderModal}
              />
            )
        }
      </>
    );
  }
}

export default Header;
