import React from 'react';
import SignUpHeader from './sign-up-header';
import AuraHeaderIcon from '../../svg-component/aura-header-icon';
import {
  setStorageInfo,
  getStorageInfo,
} from '../../../../js/utilities/helper';
import { getAuraLocalStorageKey } from '../../utilities/aura_utils';

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
        signUpComplete: true,
      });
    }
  };

  render() {
    const {
      signUpComplete,
      isHeaderModalOpen,
    } = this.state;

    // @TODO: Create component for signup complete header and return that instead of null.
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
            ? null
            : (
              <SignUpHeader
                handleSignUp={this.handleSignUp}
                isHeaderModalOpen={isHeaderModalOpen}
              />
            )
        }
      </>
    );
  }
}

export default Header;
