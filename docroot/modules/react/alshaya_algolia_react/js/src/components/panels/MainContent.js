import React from 'react'

export default function MainContent(props) {
  return (
    <main>
      <div className="c-content__region">
        <div className="region region__content clearfix">
          <div className="container-wrapper">
            {props.children}
          </div>
        </div>
      </div>
    </main>
  );
};
