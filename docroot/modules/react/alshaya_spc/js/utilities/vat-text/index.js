import React from 'react';

export default class VatText extends React.Component {
  render() {
    const { vat_text } = window.drupalSettings.alshaya_spc;
    if (vat_text !== undefined) {
      return <span className="vat-text">{vat_text}</span>;
    }

    return (null);
  }
}
