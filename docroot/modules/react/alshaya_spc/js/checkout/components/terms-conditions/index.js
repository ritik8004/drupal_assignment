import React from 'react';

export default class TermsConditions extends React.Component {
  getHtmlMarkup() {
    const termsCondition = window.drupalSettings.terms_condition || '';
    return { __html: termsCondition };
  }

  render() {
    return (
      <div className="spc-checkout-terms-conditions" dangerouslySetInnerHTML={this.getHtmlMarkup()} />
    );
  }
}
