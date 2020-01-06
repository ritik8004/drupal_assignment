import React from 'react';

export default class TermsConditions extends React.Component {

  getHtmlMarkup() {
  	let terms_condition = window.drupalSettings.terms_condition || '';
    return { __html: terms_condition };
  }

  render() {
    return (
      <div dangerouslySetInnerHTML={this.getHtmlMarkup()}/>
    ); 	
  }

}
