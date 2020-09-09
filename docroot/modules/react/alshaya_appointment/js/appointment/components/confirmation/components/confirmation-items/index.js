import React from 'react';

const ConfirmationItems = (item) => (
  <tr className="confirmation-item">
    <td className="label-wrapper"><label>{item.item.label}</label></td>
    <td className="value-wrapper"><span>{item.item.value}</span></td>
  </tr>
);

export default ConfirmationItems;
