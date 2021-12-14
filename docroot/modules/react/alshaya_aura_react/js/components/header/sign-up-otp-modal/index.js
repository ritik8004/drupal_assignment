import React from 'react';
import Popup from 'reactjs-popup';
import AuraFormSignUpOTPModal
  from '../../../../../alshaya_spc/js/aura-loyalty/components/aura-forms/aura-otp-modal-form';
import AuraFormNewAuraUserModal
  from '../../../../../alshaya_spc/js/aura-loyalty/components/aura-forms/aura-new-aura-user-form';

class SignUpOtpModal extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isNewUserModalOpen: false,
      chosenCountryCode: null,
      chosenUserMobile: null,
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

  render() {
    const {
      isNewUserModalOpen,
      chosenCountryCode,
      chosenUserMobile,
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
          />
        </Popup>
      </>
    );
  }
}

export default SignUpOtpModal;
