import React from 'react';

export default function MainContent(props) {
  const { children } = props;
  return (
    <div>
      <div className="c-content__region">
        <div className="region region__content clearfix">
          <div className="container-wrapper">
            {children}
          </div>
        </div>
      </div>
    </div>
  );
}
