import React from 'react';
import dispatchCustomEvent from '../../../../../../js/utilities/events';

class QuantityDropdown extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      qty: 1,
    };
  }

  componentDidUpdate(prevProps) {
    const { stockQty } = this.props;
    if (prevProps.stockQty !== stockQty) {
      // eslint-disable-next-line react/no-did-update-set-state
      this.setState({ qty: 1 });
    }
  }

  decrease = (e) => {
    e.preventDefault();
    this.setState((prevState) => ({ qty: prevState.qty - 1 }));

    const { qty } = this.state;
    this.dispatchQtyUpdateEvent(qty - 1);
  };

  increase = (e) => {
    e.preventDefault();
    this.setState((prevState) => ({ qty: prevState.qty + 1 }));

    const { qty } = this.state;
    this.dispatchQtyUpdateEvent(qty + 1);
  };

  dispatchQtyUpdateEvent = (qty) => {
    const {
      variantSelected, productInfo, skuCode, context,
    } = this.props;
    const priceKey = (context === 'related') ? 'final_price' : 'finalPrice';
    const price = productInfo[skuCode].variants
      ? productInfo[skuCode].variants[variantSelected][priceKey]
      : productInfo[skuCode][priceKey];
    const data = [{
      code: variantSelected,
      quantity: qty,
      amount: price * qty,
    }];
    dispatchCustomEvent('variantQuantityUpdated', { data, context });
  };

  render() {
    const { qty } = this.state;
    const { stockQty } = this.props;
    const { cartMaxQty } = drupalSettings;
    const limit = (stockQty < cartMaxQty) ? stockQty : parseInt(cartMaxQty, 10);
    const isEnabledDecreaseBtn = ((qty === limit) && (qty === 1)) || (qty === 1);
    const isEnabledIncreaseBtn = (qty === limit);
    return (
      <div className="magv2-qty-container">
        <button type="submit" className="magv2-qty-btn magv2-qty-btn--down" onClick={(e) => this.decrease(e)} disabled={isEnabledDecreaseBtn} />
        <input type="text" id="qty" className="magv2-qty-input" value={qty} name="quantity" readOnly />
        <button type="submit" className="magv2-qty-btn magv2-qty-btn--up" onClick={(e) => this.increase(e)} disabled={isEnabledIncreaseBtn} />
      </div>
    );
  }
}

export default QuantityDropdown;
