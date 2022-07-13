import React from 'react';
import Popup from 'reactjs-popup';
import { getLoyaltyOptionText } from '../../../../../../alshaya_hello_member/js/src/utilities';

export default class LoyaltyConfirmPopup extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      open: true,
    };
  }

  /**
   * Close the modal if user selected cancel.
   */
  closeModal = () => {
    const { resetPopupStatus } = this.props;
    this.setState({
      open: false,
    });
    resetPopupStatus();
  }

  /**
   * Confirm the loyalty option if user clicked on yes.
   */
  confirmLoyalty = (selectedOption) => {
    const { changeLoyaltyOption } = this.props;
    changeLoyaltyOption(selectedOption);
  }

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
            <a className="close-modal" onClick={() => this.closeModal()}>Close</a>
            <div className="loyalty-question">
              {Drupal.t('Do you want to remove all the benefits of @current_option and choose @selected_option benefits??', { '@current_option': getLoyaltyOptionText(currentOption), '@selected_option': getLoyaltyOptionText(selectedOption) }, { context: 'loyalty' })}
            </div>
            <div className="loyalty-options">
              <button
                className="loyalty-cancel"
                id="loyalty-cancel"
                type="button"
                onClick={() => this.closeModal()}
              >
                {Drupal.t('No', {}, { context: 'hello_member' })}
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
