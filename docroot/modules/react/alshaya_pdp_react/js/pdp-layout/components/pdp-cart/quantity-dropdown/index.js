import React from 'react';

const QuantityDropdown = (props) => {
  const { variantSelected, productInfo, skuCode } = props;

  let stockQty = 1;
  if (typeof productInfo[skuCode].variants !== 'undefined') {
    stockQty = productInfo[skuCode].variants[variantSelected].stock.qty;
  }
  const options = [];
  for (let i = 1; i <= productInfo[skuCode].cartMaxQty; i++) {
    if (i <= stockQty) {
      options.push(
        <option value={i}>{i}</option>,
      );
    } else {
      options.push(
        <option value={i} disabled>{i}</option>,
      );
    }
  }

  return (
    <select id="qty">
      {options}
    </select>
  );
};
export default QuantityDropdown;
