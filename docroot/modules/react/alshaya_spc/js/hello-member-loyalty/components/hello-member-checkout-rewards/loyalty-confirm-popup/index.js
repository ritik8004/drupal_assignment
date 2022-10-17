import React from 'react';
import Popup from 'reactjs-popup';

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
    let popupTitle = Drupal.t('Switch to H&M member', {}, { context: 'hello_member' });
    let popupDescription = Drupal.t('This purchase will accumulate points earned towards your H&M Membership', {}, { context: 'hello_member' });
    let earnButton = Drupal.t('Earn H&M points', {}, { context: 'hello_member' });
    let continueButton = Drupal.t('Continue with Aura Points', {}, { context: 'hello_member' });
    if (this.getLoyaltyOptionText(selectedOption) === 'Aura') {
      popupTitle = Drupal.t('Switch to Aura points', {}, { context: 'hello_member' });
      popupDescription = Drupal.t('This purchase will accumulate points earned towards your Aura Membership', {}, { context: 'hello_member' });
      earnButton = Drupal.t('Earn Aura points', {}, { context: 'hello_member' });
      continueButton = Drupal.t('Continue with H&M Points', {}, { context: 'hello_member' });
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
