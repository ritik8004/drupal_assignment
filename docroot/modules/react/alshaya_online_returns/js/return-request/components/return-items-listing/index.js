import React from 'react';
import Collapsible from 'react-collapsible';
import ReturnItemDetails from '../return-item-details';
import dispatchCustomEvent from '../../../../../js/utilities/events';

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
  handleSelectedReason = (selectedReason) => {
    if (selectedReason) {
      this.setState({
        btnDisabled: selectedReason.value === 0,
      });
    }
  }

  /**
   * Capturing selected items and enabling/disabling button
   * as per item selection.
   */
  processSelectedItems = (checked, item) => {
    if (checked) {
      this.setState((prevState) => ({
        itemsSelected: [...prevState.itemsSelected, item],
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
   * When user clicks continue button, disable the item
   * details accordion and enable refund accordion.
   */
  continueToRefundComponent = (open) => {
    dispatchCustomEvent('updateRefundAccordionState', open);
    this.setState({
      open: !open,
    });
  }

  /**
   * When item details accordion is opened, refund
   * accordion is collapsed.
   */
  disableRefundComponent = () => {
    const { open } = this.state;
    if (!open) {
      this.setState({
        open: true,
      });
    }
    dispatchCustomEvent('updateRefundAccordionState', false);
  };

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
              />
            </div>
          ))}
          <div className="continue-button-wrapper">
            <button type="button" onClick={() => this.continueToRefundComponent(open)} disabled={btnState}>
              <span className="continue-button-label">{Drupal.t('Continue', {}, { context: 'online_returns' })}</span>
            </button>
          </div>
        </Collapsible>
      </div>
    );
  }
}

export default ReturnItemsListing;
