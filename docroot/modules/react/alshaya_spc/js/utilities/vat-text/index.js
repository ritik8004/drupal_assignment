import React from 'react';

export default class VatText extends React.Component {
  render() {
    const { vat_text: vatText } = window.drupalSettings.alshaya_spc;
    if (vatText !== undefined) {
      return <span className="vat-text">{vatText}</span>;
    }

    return (null);
  }
}
