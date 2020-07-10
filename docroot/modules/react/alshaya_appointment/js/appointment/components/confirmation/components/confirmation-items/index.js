import React from 'react';

const ConfirmationItems = (item) => (
  <div className="confirmation-item">
    <label>{item.item.label}</label>
    <span>{item.item.value}</span>
  </div>
);

export default ConfirmationItems;
