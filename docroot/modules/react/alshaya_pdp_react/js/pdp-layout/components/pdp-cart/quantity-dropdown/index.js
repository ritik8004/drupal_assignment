import React from 'react';

const QuantityDropdown = (props) => {
  const { variantSelected, productInfo, skuCode } = props;
  const { cartMaxQty } = drupalSettings;

  let { stockQty } = productInfo[skuCode];
  if (typeof productInfo[skuCode].variants !== 'undefined') {
    stockQty = productInfo[skuCode].variants[variantSelected].stock.qty;
  }
  const options = [];
  for (let i = 1; i <= cartMaxQty; i++) {
    if (i <= stockQty) {
      options.push(
        <option key={i} value={i}>{i}</option>,
      );
    } else {
      options.push(
        <option key={i} value={i} disabled>{i}</option>,
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
