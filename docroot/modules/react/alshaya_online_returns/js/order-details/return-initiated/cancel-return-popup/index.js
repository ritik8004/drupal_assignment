import React from 'react';
import Popup from 'reactjs-popup';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../../js/utilities/showRemoveFullScreenLoader';
import { cancelReturnRequest } from '../../../utilities/return_api_helper';

export default class CancelReturnPopUp extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      cancelBtnState: true,
    };
  }

  /**
   * Trigger cancel return api call.
   */
  confirmCancellation = () => {
    const { returnInfo } = this.props;
    this.cancelReturnRequest(returnInfo);
  }

  cancelReturnRequest = async (returnInfo) => {
    showFullScreenLoader();
    const cancelReturn = await cancelReturnRequest(returnInfo);
    removeFullScreenLoader();

    if (hasValue(cancelReturn.error)) {
      // @todo: Handle error message for cancellation.
      return;
    }

    if (hasValue(cancelReturn.data)) {
      this.setState({ cancelBtnState: false }, () => {
        this.closeModal();
      });
    }
  }

  /**
   * Close cancel return modal.
   */
  closeModal = () => {
    const { closeCancelReturnModal } = this.props;
    const { cancelBtnState } = this.state;
    closeCancelReturnModal(cancelBtnState);
  }

  render() {
    return (
      <div className="cancel-return-popup-container">
        <Popup
          open
          className="cancel-return-confirmation"
          closeOnDocumentClick={false}
          closeOnEscape={false}
        >
          <div className="cancel-return-popup-block">
            <div className="cancel-return-heading">
              {Drupal.t('Cancel Return', { context: 'online_returns' })}
            </div>
            <a className="close-modal" onClick={() => this.closeModal()}>Close</a>
            <div className="cancel-return-question">
              {Drupal.t('Are you sure to cancel this return?', { context: 'online_returns' })}
            </div>
            <div className="cancel-return-options">
              <button
                className="cancel-return-yes"
                id="cancel-return-yes"
                type="button"
                onClick={() => this.confirmCancellation(true)}
              >
                {Drupal.t('Cancel', { context: 'online_returns' })}
              </button>
              <button
                className="cancel-return-no"
                id="cancel-return-no"
                type="button"
                onClick={() => this.closeModal()}
              >
                {Drupal.t('No, Take me back', {}, { context: 'online_returns' })}
              </button>
            </div>
          </div>
        </Popup>
      </div>
    );
  }
}
