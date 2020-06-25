import React from 'react';

class QuantityDropdown extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      qty: 1,
      stockQty: 0,
    };
  }

  componentDidMount() {
    const { variantSelected, productInfo, skuCode } = this.props;
    this.setState({
      stockQty: typeof productInfo[skuCode].variants !== 'undefined' ? productInfo[skuCode].variants[variantSelected].stock.qty : productInfo[skuCode].stockQty,
    });
  }

  componentDidUpdate(prevProps) {
    const { variantSelected, productInfo, skuCode } = this.props;

    if (productInfo[skuCode].variants[variantSelected].id
      !== prevProps.productInfo[prevProps.skuCode].variants[prevProps.variantSelected].id) {
      // eslint-disable-next-line react/no-did-update-set-state
      this.setState({
        stockQty: typeof productInfo[skuCode].variants !== 'undefined'
          ? productInfo[skuCode].variants[variantSelected].stock.qty
          : productInfo[skuCode].stockQty,
      });
    }
  }

  decrease = (e) => {
    const { qty } = this.state;
    e.preventDefault();
    this.setState({ qty: qty - 1 });
  };

  increase = (e) => {
    const { qty } = this.state;
    e.preventDefault();
    this.setState({ qty: qty + 1 });
  };

  render() {
    const { qty, stockQty } = this.state;
    const isEnabledDecreaseBtn = ((qty === stockQty) && (qty === 1)) || (qty === 1);
    const isEnabledIncreaseBtn = (qty === stockQty);
    return (
      <div className="magv2-qty-container">
        <button type="submit" className="magv2-qty-btn magv2-qty-btn--down" onClick={(e) => this.decrease(e)} disabled={isEnabledDecreaseBtn} />
        <input type="text" id="qty" className="magv2-qty-input" value={qty} readOnly />
        <button type="submit" className="magv2-qty-btn magv2-qty-btn--up" onClick={(e) => this.increase(e)} disabled={isEnabledIncreaseBtn} />
      </div>
    );
  }
}

export default QuantityDropdown;
