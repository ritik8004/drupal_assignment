import React from 'react';
import Collapsible from 'react-collapsible';
import ReturnItemDetails from '../return-item-details';
import dispatchCustomEvent from '../../../../../js/utilities/events';
import { createReturnRequest } from '../../../utilities/return_api_helper';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import { getDefaultResolutionId } from '../../../utilities/return_request_util';

class ReturnItemsListing extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      btnDisabled: true,
      itemsSelected: [],
      open: true,
    };
    this.handleSelectedReason = this.handleSelectedReason.bind(this);
    this.processSelectedItems = this.processSelectedItems.bind(this);
  }

  componentDidMount() {
    const { open } = this.state;
    // Dispatch event to disable refund accordion on page load.
    dispatchCustomEvent('updateRefundAccordionState', !open);
  }

  /**
   * If any reason is selected by customer
   * continue button will be enabled.
   */
  handleSelectedReason = (selectedReason, sku) => {
    if (selectedReason) {
      const { itemsSelected } = this.state;

      const items = itemsSelected.map((item) => {
        const data = { ...item };
        if (data.sku === sku) {
          data.reason = selectedReason.value;
        }
        return data;
      });

      this.setState({
        btnDisabled: selectedReason.value === 0,
        itemsSelected: items,
      });
    }
  }

  /**
   * Update selected quantity in state.
   */
  handleSelectedQuantity = (selectedQuantity, sku) => {
    if (selectedQuantity) {
      const { itemsSelected } = this.state;

      const items = itemsSelected.map((item) => {
        const data = { ...item };
        if (data.sku === sku) {
          data.qty_requested = selectedQuantity.value;
        }
        return data;
      });

      this.setState({
        itemsSelected: items,
      });
    }
  }

  /**
   * Capturing selected items and enabling/disabling button
   * as per item selection.
   */
  processSelectedItems = (checked, item) => {
    if (checked) {
      const itemDetails = item;

      // Add default quantity and resolution.
      itemDetails.qty_requested = 1;
      itemDetails.resolution = getDefaultResolutionId();

      this.setState((prevState) => ({
        itemsSelected: [...prevState.itemsSelected, itemDetails],
        btnDisabled: true,
      }));
    } else {
      this.setState((prevState) => ({
        itemsSelected: prevState.itemsSelected.filter((product) => product.sku !== item.sku),
      }));
    }
  }

  /**
   * Display the return items accordion trigger component.
   * On click of this component, item details div will open.
   */
  itemListHeader = () => (
    <div className="select-items-label">
      <div className="select-items-header">{ Drupal.t('Select items to return', {}, { context: 'online_returns' }) }</div>
    </div>
  );

  /**
   * When item details accordion is opened, refund
   * accordion is collapsed.
   */
  disableRefundComponent = () => {
    this.updateRefundAccordion(false);
  };

  /**
   * Process return request submit.
   */
  handleReturnSubmit = () => {
    const { btnDisabled, open } = this.state;
    const { handleReturnRequestSubmit } = this.props;

    // When user clicks continue button, disable the item
    // details accordion and enable refund accordion.
    this.updateRefundAccordion(open);

    if (!btnDisabled) {
      handleReturnRequestSubmit();
      this.createReturnRequest();
    }
  }

  /**
   * Update accordion state of refund details component.
   */
  updateRefundAccordion = (accordionState) => {
    this.setState({
      open: !accordionState,
    });

    dispatchCustomEvent('updateRefundAccordionState', accordionState);
  }

  /**
   * Create return request.
   */
  createReturnRequest = async () => {
    const { itemsSelected } = this.state;
    const returnRequest = await createReturnRequest(itemsSelected);

    if (hasValue(returnRequest.error)) {
      // @todo: Handle error display.
      return;
    }

    // On success, redirect to return confirmation page.
    // @todo: Update return confirmation URL.
    window.location.href = Drupal.url('/');
  }

  render() {
    const { btnDisabled, itemsSelected, open } = this.state;
    const { products } = this.props;
    // If no item is selected, button remains disabled.
    const btnState = !!((itemsSelected.length === 0 || btnDisabled));
    return (
      <div className="products-list-wrapper">
        <Collapsible
          trigger={this.itemListHeader()}
          open={open}
          onOpening={() => this.disableRefundComponent()}
        >
          {products.map((item) => (
            <div key={item.sku} className="item-list-wrapper">
              <ReturnItemDetails
                item={item}
                handleSelectedReason={this.handleSelectedReason}
                processSelectedItems={this.processSelectedItems}
                handleSelectedQuantity={this.handleSelectedQuantity}
              />
            </div>
          ))}
          <div className="continue-button-wrapper">
            <button
              type="button"
              disabled={btnState}
              onClick={this.handleReturnSubmit}
            >
              <span className="continue-button-label">{Drupal.t('Continue', {}, { context: 'online_returns' })}</span>
            </button>
          </div>
        </Collapsible>
      </div>
    );
  }
}

export default ReturnItemsListing;
