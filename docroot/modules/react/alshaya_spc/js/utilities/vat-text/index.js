import React from 'react';

export default class VatText extends React.Component {

  render() {
    const vat_text = this.props.vat_text;
    if (vat_text.length > 0) {
      return <span>{vat_text}</span>
    }

    return (null);
  }

}
