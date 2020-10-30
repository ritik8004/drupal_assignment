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
    const { isNotExpandable } = this.props;
    const localStorageValues = getStorageInfo(getAuraLocalStorageKey());
    if (localStorageValues) {
      this.state = {
        ...localStorageValues,
        signUpComplete: true,
        isHeaderModalOpen: !!isNotExpandable,
      };
    } else {
      this.state = {
        signUpComplete: false,
        isHeaderModalOpen: !!isNotExpandable,
      };
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
  };

  getAuraLabel = (previewClass) => {
    const { isDesktop } = this.props;

    if (isDesktop) {
      return (
        <div className={`aura-header-link ${previewClass}`}>
          <a
            className="join-aura"
            onClick={() => this.openHeaderModal()}
          >
            <AuraHeaderIcon />
          </a>
        </div>
      );
    }

    return (
      <div className={`aura-header-link ${previewClass}`}>
        <div className="aura-header-hb-menu-title">
          <span className="preview-text">{Drupal.t('Say hello to')}</span>
          <span className="join-aura"><AuraHeaderIcon /></span>
          <span
            className="aura-header-hb-menu-expand"
            onClick={() => this.openHeaderModal()}
          />
        </div>
      </div>
    );
  };

  render() {
    const {
      signUpComplete,
      isHeaderModalOpen,
      apc_identifier_number: cardNumber,
    } = this.state;

    const {
      isNotExpandable,
    } = this.props;
    const previewClass = isHeaderModalOpen === true ? 'open' : '';

    return (
      <>
        {
          !isNotExpandable
            && (
              this.getAuraLabel(previewClass)
            )
        }
        {
          signUpComplete
            ? (
              <SignUpCompleteHeader
                handleNotYou={this.handleNotYou}
                isHeaderModalOpen={isHeaderModalOpen}
                cardNumber={cardNumber}
                isNotExpandable={isNotExpandable}
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
