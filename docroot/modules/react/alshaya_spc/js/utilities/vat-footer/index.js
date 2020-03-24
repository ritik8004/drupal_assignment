import React from 'react';

const VatFooterText = () => {
  const { vat_text_footer: vatTextFooter } = window.drupalSettings.alshaya_spc;
  if (vatTextFooter !== undefined) {
    return <span className="vat-text-footer">{vatTextFooter}</span>;
  }

  return (null);
};

export default VatFooterText;
