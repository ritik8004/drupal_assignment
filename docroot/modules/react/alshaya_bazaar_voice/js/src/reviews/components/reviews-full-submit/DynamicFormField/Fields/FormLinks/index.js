import React from 'react';

const FormLinks = ({
  tnc, reviewGuide,
}) => (
  <div className="link-block">
    <div className="static-link"><a href="#">{tnc}</a></div>
    <div className="static-link"><a href="#">{reviewGuide}</a></div>
  </div>
);

export default FormLinks;
