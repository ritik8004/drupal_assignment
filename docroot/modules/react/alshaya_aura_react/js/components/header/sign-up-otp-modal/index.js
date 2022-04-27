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

    if (document.getElementById('block-alshayamainmenu')) {
      document.getElementById('block-alshayamainmenu').classList.add('aura-header-modal-open');
    }
  };

  closeNewUserModal = () => {
    this.setState({
      isNewUserModalOpen: false,
    });

    if (document.getElementById('block-alshayamainmenu')) {
      document.getElementById('block-alshayamainmenu').classList.remove('aura-header-modal-open');
    }
  };

  // Opens links card modal.
  openLinkCardModal = () => {
    const { isNewUserModalOpen } = this.state;
    // Close new user modal if open.
    if (isNewUserModalOpen) {
      this.setState({
        isNewUserModalOpen: false,
      });
    }
    this.setState({
      isLinkCardModalOpen: true,
    });
  };

  // closes links card modal.
  closeLinkCardModal = () => {
    this.setState({
      isLinkCardModalOpen: false,
    });
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
            openLinkCardModal={() => this.openLinkCardModal()}
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
            changeFormBasedOnUserAuthentication
          />
        </Popup>
      </>
    );
  }
}

export default SignUpOtpModal;
