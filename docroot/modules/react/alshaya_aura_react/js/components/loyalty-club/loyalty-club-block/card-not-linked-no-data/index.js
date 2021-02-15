import React from 'react';
import Popup from 'reactjs-popup';
import AuraLogo from '../../../../svg-component/aura-logo';
import ConditionalView
  from '../../../../../../alshaya_spc/js/common/components/conditional-view';
import AuraFormSignUpOTPModal
  from '../../../../../../alshaya_spc/js/aura-loyalty/components/aura-forms/aura-otp-modal-form';
import AuraFormNewAuraUserModal
  from '../../../../../../alshaya_spc/js/aura-loyalty/components/aura-forms/aura-new-aura-user-form';
import AuraFormLinkCardOTPModal
  from '../../../../../../alshaya_spc/js/aura-loyalty/components/aura-forms/aura-link-card-otp-modal-form';

class AuraMyAccountNoLinkedCard extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isOTPModalOpen: false,
      isNewUserModalOpen: false,
      chosenCountryCode: null,
      chosenUserMobile: null,
      isLinkCardModalOpen: false,
    };
  }

  setChosenCountryCode = (code) => {
    this.setState({
      chosenCountryCode: code,
    });
  };

  setChosenUserMobile = (code) => {
    this.setState({
      chosenUserMobile: code,
    });
  };

  openOTPModal = () => {
    this.setState({
      isOTPModalOpen: true,
    });
  };

  closeOTPModal = () => {
    this.setState({
      isOTPModalOpen: false,
    });
  };

  openLinkCardModal = () => {
    this.setState({
      isLinkCardModalOpen: true,
    });
  };

  closeLinkCardModal = () => {
    this.setState({
      isLinkCardModalOpen: false,
    });
  };

  openNewUserModal = () => {
    this.setState({
      isNewUserModalOpen: true,
    });
  };

  closeNewUserModal = () => {
    this.setState({
      isNewUserModalOpen: false,
    });
  };

  render() {
    const {
      isOTPModalOpen,
      isNewUserModalOpen,
      chosenCountryCode,
      chosenUserMobile,
      isLinkCardModalOpen,
    } = this.state;

    return (
      <div className="aura-myaccount-no-linked-card-wrapper no-card-found fadeInUp">
        <div className="aura-logo">
          <ConditionalView condition={window.innerWidth > 1024}>
            <AuraLogo stacked="vertical" />
          </ConditionalView>
          <ConditionalView condition={window.innerWidth < 1025}>
            <AuraLogo stacked="horizontal" />
          </ConditionalView>
        </div>
        <div className="aura-myaccount-no-linked-card-description no-card-found">
          <div className="link-your-card">
            { Drupal.t('Already AURA Member?') }
            <div
              className="btn"
              onClick={() => this.openLinkCardModal()}
            >
              { Drupal.t('Link your card') }
            </div>
          </div>
          <div className="sign-up">
            { Drupal.t('Ready to be Rewarded?') }
            <div
              className="btn"
              onClick={() => this.openOTPModal()}
            >
              { Drupal.t('Sign up') }
            </div>
            <Popup
              className="aura-modal-form otp-modal"
              open={isOTPModalOpen}
              closeOnEscape={false}
              closeOnDocumentClick={false}
            >
              <AuraFormSignUpOTPModal
                closeOTPModal={() => this.closeOTPModal()}
                openNewUserModal={() => this.openNewUserModal()}
                setChosenCountryCode={this.setChosenCountryCode}
                setChosenUserMobile={this.setChosenUserMobile}
                chosenCountryCode={chosenCountryCode}
              />
            </Popup>
            <Popup
              className="aura-modal-form new-aura-user"
              open={isNewUserModalOpen}
              closeOnEscape={false}
              closeOnDocumentClick={false}
            >
              <AuraFormNewAuraUserModal
                chosenCountryCode={chosenCountryCode}
                chosenUserMobile={chosenUserMobile}
                closeNewUserModal={() => this.closeNewUserModal()}
              />
            </Popup>
            <Popup
              className="aura-modal-form link-card-otp-modal"
              open={isLinkCardModalOpen}
              closeOnEscape={false}
              closeOnDocumentClick={false}
            >
              <AuraFormLinkCardOTPModal
                closeLinkCardOTPModal={() => this.closeLinkCardModal()}
                setChosenCountryCode={this.setChosenCountryCode}
                chosenCountryCode={chosenCountryCode}
              />
            </Popup>
          </div>
        </div>
      </div>
    );
  }
}
export default AuraMyAccountNoLinkedCard;
