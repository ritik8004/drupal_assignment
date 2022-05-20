import React from 'react';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import ReturnIndividualItem from '../../../return-request/components/return-individual-item';
import { getPrintLabelStatus } from '../../../utilities/online_returns_util';
import CancelReturnPopUp from '../cancel-return-popup';

class ProcessedItem extends React.Component {
  constructor(props) {
    const { returnData } = props;
    super(props);
    this.state = {
      popup: false,
      cancelBtnState: true,
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
  getPrintLabelPdfLink = () => '#';

  render() {
    const { popup, cancelBtnState, showPrintLabelBtn } = this.state;
    const { returnData, returnStatus } = this.props;
    return (
      <div key={returnData.returnInfo.increment_id} className="return-status-header">
        <div className="return-status-wrapper">
          <div className="return-status">
            <span className={`status-label ${returnStatus}`}>{returnData.returnInfo.extension_attributes.customer_status}</span>
            <span className="status-message">
              {' - '}
              {returnData.returnInfo.extension_attributes.description}
            </span>
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
                  <span className="cancel-button-label">{Drupal.t('Cancel Return', {}, { context: 'online_returns' })}</span>
                </button>
              </div>
            </ConditionalView>
          </div>
        </div>
        <div className="return-id">
          {Drupal.t('Return ID: @return_id', { '@return_id': returnData.returnInfo.increment_id }, { context: 'online_returns' })}
        </div>
        <ConditionalView condition={popup}>
          <CancelReturnPopUp
            returnInfo={returnData.returnInfo}
            closeCancelReturnModal={this.closeCancelReturnModal}
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
