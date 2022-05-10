import React from 'react';
import HeroImage from './hero-image';
import EgiftsCardList from './egifts-card-list';
import EgiftCardAmount from './egift-card-amount';
import { getTextAreaMaxLength } from '../../utilities';

export default class EgiftCardsListStepOne extends React.Component {
  constructor(props) {
    super(props);

    // Set ref for openAmount field.
    this.ref = React.createRef();

    this.state = {
      items: props.items,
      selectedItem: props.items[0],
    };
  }


  /**
   * Handle egift card select from list.
   */
  handleEgiftSelect = (id) => {
    // Reset error message to empty.
    if (document.getElementById('open-amount-error') !== null) {
      document.getElementById('open-amount-error').innerHTML = '';
    }

    if (document.getElementById('textarea-count')) {
      // Reset count on textarea.
      document.getElementById('textarea-count').innerHTML = getTextAreaMaxLength();
    }

    // Remove readonly from open amount field on eGift Select.
    if (this.ref.current !== null) {
      this.ref.current.querySelector('input').removeAttribute('readOnly');
    }

    // Get all egift card items.
    const { items } = this.state;

    items.forEach((item) => {
      // Set state for selected eGift card.
      // If item id matches then set as selected item.
      if (item.id === id) {
        this.setState({
          selectedItem: item,
        });
      }
    });
  }

  render() {
    const { items, selectedItem } = this.state;
    const { handleAmountSelect } = this.props;

    return (
      <>
        <p className="step-title fadeInUp">
          { Drupal.t('1. Select your style and card amount', {}, { context: 'egift' }) }
        </p>
        <div className="step-wrapper step-one-wrapper fadeInUp">
          <HeroImage item={selectedItem} />
          <div className="egift-card-purchase-config-wrapper">
            <EgiftsCardList
              items={items}
              selected={selectedItem}
              handleEgiftSelect={this.handleEgiftSelect}
            />
            <EgiftCardAmount
              selected={selectedItem}
              handleAmountSelect={handleAmountSelect}
              myAccountLabel={false}
              field={this.ref}
            />
            <input type="hidden" name="egift-sku" value={selectedItem.sku} />
          </div>
        </div>
      </>
    );
  }
}
