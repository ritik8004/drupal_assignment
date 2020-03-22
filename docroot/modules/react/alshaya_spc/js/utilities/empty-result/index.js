import React from 'react';

const EmptyResult = (props) => {
  const { Message } = props;
  return (
    <div className="spc-empty-container">
      <div className="spc-empty-text">{Message}</div>
      <div className="spc-shopping-link"><a href="/home">{Drupal.t('go shopping')}</a></div>
    </div>
  );
};

export default EmptyResult;
