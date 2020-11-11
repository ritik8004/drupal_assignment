import React from 'react';

const ClickCollectSearchSVG = () => (
  <svg xmlns="http://www.w3.org/2000/svg" xlink="http://www.w3.org/1999/xlink" width="34" height="34" viewBox="0 0 34 34">
    <defs>
      <path id="prefix__searcha" d="M27 25.586L21.314 19.9C22.367 18.546 23 16.848 23 15c0-4.418-3.582-8-8-8s-8 3.582-8 8 3.582 8 8 8c1.848 0 3.545-.633 4.9-1.686L25.586 27 27 25.586zM15 21c-3.308 0-6-2.692-6-6s2.692-6 6-6 6 2.692 6 6-2.692 6-6 6z" />
    </defs>
    <g fill="none" fillRule="evenodd">
      <mask id="prefix__searchb" fill="#fff">
        <use href="#prefix__searcha" />
      </mask>
      <use fill="#000" href="#prefix__searcha" />
      <g fill="#000" mask="url(#prefix__searchb)">
        <path d="M0 0H34V34H0z" transform="rotate(-180 17 17)" />
      </g>
    </g>
  </svg>

);

export default ClickCollectSearchSVG;
