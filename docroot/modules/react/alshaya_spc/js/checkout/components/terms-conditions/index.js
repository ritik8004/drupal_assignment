import React from 'react';
import parse from 'html-react-parser';

export default class TermsConditions extends React.Component {
  getHtmlMarkup = () => {
    const termsCondition = window.drupalSettings.terms_condition || '';
    return termsCondition;
  };

  render() {
    return (
      <div className="spc-checkout-terms-conditions">
        {parse(this.getHtmlMarkup())}
      </div>
    );
  }
}
