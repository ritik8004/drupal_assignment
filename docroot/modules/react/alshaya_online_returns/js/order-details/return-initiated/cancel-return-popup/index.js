import React from 'react';
import Popup from 'reactjs-popup';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import logger from '../../../../../js/utilities/logger';
import { removeFullScreenLoader, showFullScreenLoader } from '../../../../../js/utilities/showRemoveFullScreenLoader';
import { cancelReturnRequest } from '../../../utilities/return_api_helper';
import { getPreparedOrderGtm, getProductGtmInfo } from '../../../utilities/online_returns_gtm_util';

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
    const { returnData } = this.props;
    this.cancelReturnRequest(returnData.returnInfo);
  }

  cancelReturnRequest = async (returnInfo) => {
    showFullScreenLoader();
    const cancelReturn = await cancelReturnRequest(returnInfo);
    removeFullScreenLoader();

    if (hasValue(cancelReturn.error)) {
      const { handleErrorMessage } = this.props;
      logger.error('Error while trying to cancel the return request. Data: @data.', {
        '@data': cancelReturn,
      });
      handleErrorMessage(drupalSettings.globalErrorMessage);
      this.closeModal();

      return;
    }

    if (hasValue(cancelReturn.data)) {
      const { returnData } = this.props;

      // Push the required info to GTM.
      if (hasValue(returnData) && hasValue(returnData.items)) {
        Drupal.alshayaSeoGtmPushReturn(
          getProductGtmInfo(returnData.items),
          getPreparedOrderGtm('cancelreturnconfirmed', returnInfo),
          'cancelreturnconfirmed',
        );
      }

      this.setState({ cancelBtnState: false }, () => {
        this.closeModal();
        window.location.reload();
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
              {Drupal.t('Cancel Return', {}, { context: 'online_returns' })}
            </div>
            <a className="close-modal" onClick={() => this.closeModal()}>Close</a>
            <div className="cancel-return-question">
              {Drupal.t('Are you sure you would like to cancel this return request?', {}, { context: 'online_returns' })}
            </div>
            <div className="cancel-return-options">
              <button
                className="cancel-return-yes"
                id="cancel-return-yes"
                type="button"
                onClick={() => this.confirmCancellation(true)}
              >
                {Drupal.t('Yes')}
              </button>
              <button
                className="cancel-return-no"
                id="cancel-return-no"
                type="button"
                onClick={() => this.closeModal()}
              >
                {Drupal.t('No')}
              </button>
            </div>
          </div>
        </Popup>
      </div>
    );
  }
}
