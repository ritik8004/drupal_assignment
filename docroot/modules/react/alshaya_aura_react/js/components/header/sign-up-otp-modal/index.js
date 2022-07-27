import React from 'react';
import Popup from 'reactjs-popup';
import AuraFormSignUpOTPModal
  from '../../../../../alshaya_spc/js/aura-loyalty/components/aura-forms/aura-otp-modal-form';
import AuraFormNewAuraUserModal
  from '../../../../../alshaya_spc/js/aura-loyalty/components/aura-forms/aura-new-aura-user-form';
import AuraFormLinkCardOTPModal
  from '../../../../../alshaya_spc/js/aura-loyalty/components/aura-forms/aura-link-card-otp-modal-form';

class SignUpOtpModal extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
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

  openNewUserModal = () => {
    this.setState({
      isNewUserModalOpen: true,
    });

    if (document.querySelector('.block-alshaya-main-menu')) {
      document.querySelector('.block-alshaya-main-menu').classList.add('aura-header-modal-open');
    }
  };

  closeNewUserModal = () => {
    this.setState({
      isNewUserModalOpen: false,
    });

    if (document.querySelector('.block-alshaya-main-menu')) {
      document.querySelector('.block-alshaya-main-menu').classList.remove('aura-header-modal-open');
    }
  };

  /**
   * Toggles Link card Modal visibility based on passed value.
   *
   * @param toggle
   *   True will show the Already a member Modal.
   *   And hide the New user Modal.
   */
  toggleLinkCardModal = (toggle) => {
    this.setState({
      isLinkCardModalOpen: toggle,
      isNewUserModalOpen: !toggle,
    });
  };

  /**
   * Opens OTP Modal and Closes Already a member Popup.
   */
  openOTPModal = () => {
    const {
      openOTPModal,
    } = this.props;
    this.setState({
      isLinkCardModalOpen: false,
    });
    openOTPModal();
  };

  render() {
    const {
      isNewUserModalOpen,
      chosenCountryCode,
      chosenUserMobile,
      isLinkCardModalOpen,
    } = this.state;

    const {
      isOTPModalOpen,
      closeOTPModal,
    } = this.props;

    return (
      <>
        <Popup
          className="aura-modal-form otp-modal"
          open={isOTPModalOpen}
          closeOnEscape={false}
          closeOnDocumentClick={false}
        >
          <AuraFormSignUpOTPModal
            closeOTPModal={() => closeOTPModal()}
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
            openLinkCardModal={() => this.toggleLinkCardModal(true)}
          />
        </Popup>
        <Popup
          className="aura-modal-form link-card-otp-modal"
          open={isLinkCardModalOpen}
          closeOnEscape={false}
          closeOnDocumentClick={false}
        >
          <AuraFormLinkCardOTPModal
            closeLinkCardOTPModal={() => this.setState({
              isLinkCardModalOpen: false,
            })}
            openOTPModal={() => this.openOTPModal()}
            setChosenCountryCode={this.setChosenCountryCode}
            chosenCountryCode={chosenCountryCode}
          />
        </Popup>
      </>
    );
  }
}

export default SignUpOtpModal;
