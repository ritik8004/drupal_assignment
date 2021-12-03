import React from 'react';
import HeroImage from './hero-image';
import EgiftsCardList from './egifts-card-list';
import EgiftCardAmount from './egift-card-amount';

export default class EgiftCardsListStepOne extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      items: props.items,
      selectedItem: props.items[0],
    };
  }

  handleEgiftSelect = (id) => {
    // Get all egift card items.
    const { items } = this.state;

    items.forEach((item) => {
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

    const style = {
      display: 'flex',
    };

    return (
      <>
        <p className="step-title" style={{ width: '100%' }}>
          { Drupal.t('1. Select your style and card amount', {}, { context: 'egift' }) }
        </p>
        <div className="step-one-wrapper" style={style}>
          <HeroImage item={selectedItem} />
          <EgiftsCardList
            items={items}
            selected={selectedItem}
            handleEgiftSelect={this.handleEgiftSelect}
          />
          <EgiftCardAmount selected={selectedItem} />
        </div>
      </>
    );
  }
}
