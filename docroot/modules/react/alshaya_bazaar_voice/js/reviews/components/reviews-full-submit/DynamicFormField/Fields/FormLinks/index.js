import React from 'react';

const FormLinks = ({
  tnc, reviewGuide
}) => {
  return (
    <div className="link-block">
      <div className="static-link"><a href="#">{tnc}</a></div>
      <div className="static-link"><a href="#">{reviewGuide}</a></div>
    </div>
  );
};

export default FormLinks;
