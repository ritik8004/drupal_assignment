import React from 'react';
import Popup from 'reactjs-popup';
import AuraFormLinkCardOTPModal from '../../../aura-forms/aura-link-card-otp-modal-form';
import getStringMessage from '../../../../../utilities/strings';

class LinkYourCardMessage extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      chosenCountryCode: null,
      isLinkCardModalOpen: false,
    };
  }

  setChosenCountryCode = (code) => {
    this.setState({
      chosenCountryCode: code,
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

  render() {
    const {
      chosenCountryCode,
      isLinkCardModalOpen,
    } = this.state;

    return (
      <>
        <div className="spc-aura-link-your-card-message">
          {getStringMessage('auto_accrual_message')}
          <span
            onClick={() => this.openLinkCardModal()}
          >
            {getStringMessage('link_your_card_now')}
          </span>
        </div>
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
      </>
    );
  }
}

export default LinkYourCardMessage;
