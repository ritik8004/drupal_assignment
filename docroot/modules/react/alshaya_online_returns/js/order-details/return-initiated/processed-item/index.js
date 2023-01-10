import React from 'react';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import ReturnIndividualItem from '../../../return-request/components/return-individual-item';
import { getPrintLabelStatus, getCancelButtonStatus } from '../../../utilities/online_returns_util';
import CancelReturnPopUp from '../cancel-return-popup';

class ProcessedItem extends React.Component {
  constructor(props) {
    const { returnData } = props;
    super(props);
    this.state = {
      popup: false,
      cancelBtnState: getCancelButtonStatus(returnData),
      showPrintLabelBtn: getPrintLabelStatus(returnData),
    };
  }

  /**
   * Process return request confirmation.
   */
  showCancelReturnPopup = () => {
    this.setState({
      popup: true,
    });
  }

  /**
   * To close the cancel return modal.
   */
  closeCancelReturnModal = (cancelBtnState) => {
    this.setState({
      popup: false,
      cancelBtnState,
    });
  };

  /**
   * Returns the return print label link.
   */
  getPrintLabelPdfLink = () => {
    const { uid } = drupalSettings.user;
    const { returnData } = this.props;
    // Extract the return id and order id from the return data.
    const { order_increment_id: orderId, entity_id: returnId } = returnData.returnInfo;
    // Encode the return entity id.
    const encodedReturnId = btoa(JSON.stringify({
      return_id: returnId,
    }));

    return Drupal.url(`user/${uid}/order/${orderId}/return/${encodedReturnId}/label`);
  }

  render() {
    const { popup, cancelBtnState, showPrintLabelBtn } = this.state;
    const { returnData, handleErrorMessage } = this.props;
    const returnStatus = returnData.returnInfo.extension_attributes.customer_status_key;
    const returnStatusClass = Drupal.cleanCssIdentifier(returnStatus);
    return (
      <div key={returnData.returnInfo.increment_id} className="return-status-header">
        <div className="return-status-wrapper">
          <div className="return-status-id-container">
            <div className="return-status">
              <span className={`status-label ${returnStatusClass}`}>{returnData.returnInfo.extension_attributes.customer_status}</span>
              { returnData.returnInfo.extension_attributes.description && (
                <span className="status-message">
                  {' - '}
                  {returnData.returnInfo.extension_attributes.description}
                </span>
              )}
            </div>
            <div className="return-id">
              {Drupal.t('Return ID: @return_id', { '@return_id': returnData.returnInfo.increment_id }, { context: 'online_returns' })}
            </div>
          </div>
          <div className="print-cancel-wrapper">
            <ConditionalView condition={showPrintLabelBtn}>
              <div className="print-return-label-wrapper">
                <a className="print-label-button" href={this.getPrintLabelPdfLink()}>
                  {Drupal.t('Print Return Label', {}, { context: 'online_returns' })}
                </a>
              </div>
            </ConditionalView>
            <ConditionalView condition={cancelBtnState}>
              <div className="cancel-return-button-wrapper">
                <button
                  type="button"
                  onClick={this.showCancelReturnPopup}
                >
                  <div className="cancel-button-text">
                    <span className="cancel-button-label">{Drupal.t('Cancel Return', {}, { context: 'online_returns' })}</span>
                  </div>
                </button>
              </div>
            </ConditionalView>
          </div>
        </div>
        <ConditionalView condition={popup}>
          <CancelReturnPopUp
            returnData={returnData}
            closeCancelReturnModal={this.closeCancelReturnModal}
            handleErrorMessage={handleErrorMessage}
          />
        </ConditionalView>
        <ConditionalView condition={hasValue(returnData.items)}>
          {returnData.items.map((item) => (
            <div className="item-list-wrapper" key={item.sku}>
              <ReturnIndividualItem key={item.sku} item={item} />
            </div>
          ))}
        </ConditionalView>
      </div>
    );
  }
}

export default ProcessedItem;
