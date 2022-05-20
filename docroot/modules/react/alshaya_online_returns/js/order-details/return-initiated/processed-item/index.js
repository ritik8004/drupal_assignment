import React from 'react';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import ReturnIndividualItem from '../../../return-request/components/return-individual-item';
import CancelReturnPopUp from '../cancel-return-popup';

class ProcessedItem extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      popup: false,
      cancelBtnState: true,
      showPrintLabelBtn: false,
    };
  }

  componentDidMount() {
    const { returnData } = this.props;
    const {
      awb_path: AwbPath,
      is_picked: isPicked,
      is_closed: isClosed,
    } = returnData.returnInfo.extension_attributes;
    // Set the `showPrintLabelBtn` to true if awb path is available.
    if (hasValue(AwbPath)
      && hasValue(isPicked)
      && !hasValue(isClosed)) {
      this.setState({
        showPrintLabelBtn: true,
      });
    }
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
    const { returnData, returnStatus, returnMessage } = this.props;

    // @todo: Breaking/Grouping of return items as per status.
    // @todo: Items will be listed under specific return statuses.
    return (
      <>
        <div className="return-status-header">
          <div className="return-status-wrapper">
            <div className="return-status">
              <span className="status-label">{returnStatus}</span>
              <span className="status-message">{returnMessage}</span>
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
                  <span className="cancel-button-label">{Drupal.t('Cancel Return', {}, { context: 'online_returns' })}</span>
                </button>
              </div>
            </ConditionalView>
          </div>
        </div>
        <ConditionalView condition={popup}>
          <CancelReturnPopUp
            returnInfo={returnData.returnInfo}
            closeCancelReturnModal={this.closeCancelReturnModal}
          />
        </ConditionalView>
        <ConditionalView condition={hasValue(returnData.items)}>
          {returnData.items.map((item) => (
            <div className="item-list-wrapper">
              <ReturnIndividualItem key={item.id} item={item} />
            </div>
          ))}
        </ConditionalView>
      </>
    );
  }
}

export default ProcessedItem;
