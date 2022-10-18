import React from 'react';
import Popup from 'reactjs-popup';
import getStringMessage from '../../../../utilities/strings';

export default class LoyaltyConfirmPopup extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      open: props.showLoyaltyPopup,
    };
  }

  /**
   * Close the modal if user selected cancel.
   */
  closeModal = () => {
    const { resetPopupStatus } = this.props;
    resetPopupStatus(false);
  }

  /**
   * Confirm the loyalty option if user clicked on yes.
   */
  confirmLoyalty = (selectedOption) => {
    const { changeLoyaltyOption } = this.props;
    changeLoyaltyOption(selectedOption);
  }

  /**
   * Utility function to get hello member points for given price.
   */
  getLoyaltyOptionText = (option) => {
    if (option === 'hello_member') {
      const { brandMembershipText } = drupalSettings.helloMember;
      return brandMembershipText;
    } if (option === 'aura') {
      return Drupal.t('Aura', {}, { context: 'hello_member' });
    }
    return null;
  };

  render() {
    const { open } = this.state;
    const { selectedOption } = this.props;
    let popupTitle = getStringMessage('hm_popup_title');
    let popupDescription = getStringMessage('hm_popup_description');
    let earnButton = getStringMessage('hm_earn_points');
    let continueButton = getStringMessage('aura_continue_points');

    if (this.getLoyaltyOptionText(selectedOption) === 'Aura') {
      popupTitle = getStringMessage('aura_popup_title');
      popupDescription = getStringMessage('aura_popup_description');
      earnButton = getStringMessage('aura_earn_points');
      continueButton = getStringMessage('hm_continue_points');
    }

    return (
      <div className="loyalty-popup-container">
        <Popup
          open={open}
          className="loyalty-confirmation"
          closeOnDocumentClick={false}
          closeOnEscape={false}
        >
          <div className="loyalty-popup-block">
            <div className="loyalty-popup-title">
              <span>{popupTitle}</span>
              <a className="close-modal" onClick={() => this.closeModal()} />
            </div>
            <div className="loyalty-question">
              {popupDescription}
            </div>
            <div className="loyalty-options">
              <button
                className="loyalty-yes"
                id="loyalty-yes"
                type="button"
                onClick={() => this.confirmLoyalty(selectedOption)}
              >
                {earnButton}
              </button>
              <button
                className="loyalty-cancel"
                id="loyalty-cancel"
                type="button"
                onClick={() => this.closeModal()}
              >
                {continueButton}
              </button>
            </div>
          </div>
        </Popup>
      </div>
    );
  }
}
