import React from 'react';

export default class EditAddressSVG extends React.Component {
  render() {
    return (
      <svg className="edit-address-svg" xmlns="http://www.w3.org/2000/svg" width="34" height="35" viewBox="0 0 34 35">
        <defs>
          <path id="a" d="M26.54 25.51c.393 0 .719.326.719.703a.707.707 0 0 1-.606.697l-.097.007H7.803a.717.717 0 0 1-.703-.704c0-.346.26-.649.606-.697l.097-.006h18.736zM19.703 7.215l.087.074 3.289 3.329a.73.73 0 0 1 .074.925l-.074.087-11.475 11.495a.683.683 0 0 1-.386.198l-.11.008-3.323.017a.685.685 0 0 1-.497-.206.713.713 0 0 1-.197-.402l-.009-.113.017-3.345c0-.151.055-.291.139-.411l.067-.087 9.18-9.179L18.78 7.29a.727.727 0 0 1 .923-.074zm-2.721 3.9l-8.461 8.476-.017 2.316 2.33-.017 8.46-8.459-2.312-2.316zM19.295 8.8l-1.302 1.304 2.312 2.316 1.302-1.304L19.295 8.8z" />
        </defs>
        <g fill="none" fillRule="evenodd" transform="translate(0 1)">
          <mask id="b" fill="#fff">
            <use href="#a" />
          </mask>
          <use fill="#000" />
          <g fill="#DADADA" mask="url(#b)">
            <path d="M34 34H0V0h34z" />
          </g>
        </g>
      </svg>
    );
  }
}
