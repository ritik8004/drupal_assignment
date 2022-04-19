import React from 'react';
import Collapsible from 'react-collapsible';
import ReturnItemDetails from '../return-item-details';
import dispatchCustomEvent from '../../../../../js/utilities/events';
import { getDefaultResolutionId } from '../../../utilities/return_request_util';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

class ReturnItemsListing extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      btnDisabled: true,
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
      const { handleSelectedItems, itemsSelected } = this.props;

      const items = itemsSelected.map((item) => {
        const data = { ...item };
        if (data.sku === sku) {
          data.reason = selectedReason.value;
        }
        return data;
      });

      this.setState({
        btnDisabled: selectedReason.value === 0,
      });

      handleSelectedItems(items);
    }
  }

  /**
   * Update selected quantity in state.
   */
  handleSelectedQuantity = (selectedQuantity, sku) => {
    if (selectedQuantity) {
      const { handleSelectedItems, itemsSelected } = this.props;

      const items = itemsSelected.map((item) => {
        const data = { ...item };
        if (data.sku === sku) {
          data.qty_requested = selectedQuantity.value;
        }
        return data;
      });

      handleSelectedItems(items);
    }
  }

  /**
   * Capturing selected items and enabling/disabling button
   * as per item selection.
   */
  processSelectedItems = (checked, item) => {
    const { handleSelectedItems, itemsSelected } = this.props;

    if (checked) {
      const itemDetails = item;

      // Add default quantity and resolution.
      itemDetails.qty_requested = 1;
      itemDetails.resolution = getDefaultResolutionId();

      this.setState({
        btnDisabled: true,
      });
      handleSelectedItems([...itemsSelected, itemDetails]);
    } else {
      handleSelectedItems(itemsSelected.filter((product) => product.sku !== item.sku));
    }
  }

  /**
   * Display the return items accordion trigger component.
   * On click of this component, item details div will open.
   */
  itemListHeader = (products) => {
    const count = products.length;

    if (hasValue(products) && count > 0) {
      return (
        <div className="select-items-label">
          <span className="select-items-header">{ Drupal.t('1. Select items to return', {}, { context: 'online_returns' }) }</span>
          <span className="items-count">
            {/* @todo: Plural translation not working as expected. */}
            {Drupal.formatPlural(count, '(1 item)', '(@count items)', {}, { context: 'online_returns' })}
          </span>
        </div>
      );
    }
    return null;
  }

  /**
   * When item details accordion is opened, refund
   * accordion is collapsed.
   */
  disableRefundComponent = () => {
    this.updateRefundAccordion(false);
  };

  /**
   * Process return request continue.
   */
  handleReturnContinue = () => {
    const { open } = this.state;

    // When user clicks continue button, disable the item
    // details accordion and enable refund accordion.
    this.updateRefundAccordion(open);
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

  render() {
    const { btnDisabled, open } = this.state;
    const { products, itemsSelected } = this.props;
    // If no item is selected, button remains disabled.
    const btnState = !!((itemsSelected.length === 0 || btnDisabled));
    return (
      <div className="products-list-wrapper">
        <Collapsible
          trigger={this.itemListHeader(products)}
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
              onClick={this.handleReturnContinue}
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
