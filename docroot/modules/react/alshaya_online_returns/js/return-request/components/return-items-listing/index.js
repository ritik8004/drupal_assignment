import React from 'react';
import ReturnItemDetails from '../return-item-details';

class ReturnItemsListing extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      btnDisabled: true,
      itemsSelected: [],
    };
    this.handleSelectedReason = this.handleSelectedReason.bind(this);
    this.processSelectedItems = this.processSelectedItems.bind(this);
  }

  /**
   * If any reason is selected by customer
   * continue button will be enabled.
   */
  handleSelectedReason = (selectedReason) => {
    if (selectedReason.value !== 0) {
      this.setState({
        btnDisabled: false,
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

  render() {
    const { btnDisabled, itemsSelected } = this.state;
    const { products } = this.props;
    // If no item is selected, button remains disabled.
    const selected = !(itemsSelected.length === 0 || btnDisabled);
    return (
      <div className="products-list-wrapper">
        <div className="select-items-label">
          <div className="select-items-header">{ Drupal.t('Select items to return') }</div>
        </div>
        {products.map((item) => (
          <div key={item.name} className="item-list-wrapper">
            <ReturnItemDetails
              item={item}
              handleSelectedReason={this.handleSelectedReason}
              processSelectedItems={this.processSelectedItems}
            />
          </div>
        ))}
        <div className="continue-button-wrapper">
          <button type="button" btnDisabled={selected}>
            <span className="continue-button-label">{Drupal.t('Continue')}</span>
          </button>
        </div>
      </div>
    );
  }
}

export default ReturnItemsListing;
