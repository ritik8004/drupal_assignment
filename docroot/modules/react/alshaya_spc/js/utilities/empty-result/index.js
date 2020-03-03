import React from 'react';

class EmptyResult extends React.Component {
  render() {
    return (
      <div className="spc-empty-container">
        <div className="spc-empty-text">{this.props.Message}</div>
        <div className="spc-shopping-link"><a href="/home">{Drupal.t('go shopping')}</a></div>
      </div>
    );
  }
}

export default EmptyResult;
