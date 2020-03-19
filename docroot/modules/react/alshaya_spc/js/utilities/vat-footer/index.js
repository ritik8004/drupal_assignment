import React from 'react';

export default class VatFooterText extends React.Component {
  render() {
    const { vat_text_footer: vatTextFooter } = window.drupalSettings.alshaya_spc;
    if (vatTextFooter !== undefined) {
      return <span className="vat-text-footer">{vatTextFooter}</span>;
    }

    return (null);
  }
}
