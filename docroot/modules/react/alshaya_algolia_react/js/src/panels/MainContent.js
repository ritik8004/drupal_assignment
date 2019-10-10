import React from 'react'

export default function MainContent(props) {
  return (
    <main>
      <div class="c-content__region">
        <div class="region region__content clearfix">
          <div className="container-wrapper">
            {props.children}
          </div>
        </div>
      </div>
    </main>
  );
};
