import React from 'react';

export default class VatFooterText extends React.Component {
  render() {
    const { vat_text_footer } = window.drupalSettings.alshaya_spc;
    if (vat_text_footer !== undefined) {
      return <span className="vat-text-footer">{vat_text_footer}</span>;
    }

    return (null);
  }
}
