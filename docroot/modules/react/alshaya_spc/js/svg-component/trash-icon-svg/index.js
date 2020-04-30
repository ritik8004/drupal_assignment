import React from 'react';

const TrashIconSVG = () => (
  <svg xmlns="http://www.w3.org/2000/svg" xlink="http://www.w3.org/1999/xlink" width="34" height="34" viewBox="0 0 34 34">
    <defs>
      <path id="prefix__tta" d="M7.125 10.914h.823l1.92 16.052c.036.384.366.659.75.659h12.764c.384 0 .695-.275.75-.659l1.92-16.052h.823c.42 0 .75-.33.75-.75 0-.421-.33-.75-.75-.75h-5.742V7.124c0-.42-.33-.75-.75-.75h-6.766c-.42 0-.75.33-.75.75v2.288H7.124c-.42 0-.749.33-.749.75 0 .422.347.751.75.751zm7.242-3.038h5.266v1.537h-5.266V7.876zm10.167 3.038l-1.81 15.228H11.276l-1.81-15.228h15.068zm-10.021 2.453c-.42 0-.75.33-.75.75v8.987c0 .421.33.75.75.75s.75-.329.75-.75v-8.987c0-.42-.33-.75-.75-.75zm4.974 0c-.42 0-.75.33-.75.75v8.987c0 .421.348.75.75.75.42 0 .75-.329.75-.75v-8.987c0-.42-.33-.75-.75-.75z" />
    </defs>
    <g fill="none" fillRule="evenodd">
      <mask id="prefix__ttb" fill="#fff">
        <use href="#prefix__tta" />
      </mask>
      <use fill="#000" href="#prefix__tta" />
      <g fill="#DADADA" mask="url(#prefix__ttb)">
        <path d="M0 0H34V34H0z" transform="rotate(-180 17 17)" />
      </g>
    </g>
  </svg>

);

export default TrashIconSVG;
