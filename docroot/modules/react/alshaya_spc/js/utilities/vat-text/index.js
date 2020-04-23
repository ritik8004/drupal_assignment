import React from 'react';

const VatText = () => {
  const { vat_text: vatText } = window.drupalSettings.alshaya_spc;
  if (vatText !== undefined) {
    return <span className="vat-text">{vatText}</span>;
  }

  return (null);
};

export default VatText;
