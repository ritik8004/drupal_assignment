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
    if (option === 'hello_member_loyalty') {
      return Drupal.t('H&M membership', {}, { context: 'hello_member' });
    } if (option === 'aura_loyalty') {
      return Drupal.t('Aura', {}, { context: 'hello_member' });
    }
    return null;
  };

  render() {
    const { open } = this.state;
    const { currentOption, selectedOption } = this.props;
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
              <span>{Drupal.t('Confirm the Loyalty', {}, { context: 'hello_member' })}</span>
              <a className="close-modal" onClick={() => this.closeModal()} />
            </div>
            <div className="loyalty-question">
              {Drupal.t('Do you want to remove all the benefits of @current_option and choose @selected_option benefits??', { '@current_option': this.getLoyaltyOptionText(currentOption), '@selected_option': this.getLoyaltyOptionText(selectedOption) }, { context: 'loyalty' })}
            </div>
            <div className="loyalty-options">
              <button
                className="loyalty-cancel"
                id="loyalty-cancel"
                type="button"
                onClick={() => this.closeModal()}
              >
                {Drupal.t('Cancel', {}, { context: 'hello_member' })}
              </button>
              <button
                className="loyalty-yes"
                id="loyalty-yes"
                type="button"
                onClick={() => this.confirmLoyalty(selectedOption)}
              >
                {Drupal.t('Yes', {}, { context: 'hello_member' })}
              </button>
            </div>
          </div>
        </Popup>
      </div>
    );
  }
}
