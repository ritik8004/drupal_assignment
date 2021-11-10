import React from 'react';

const SocialShareSVG = () => (
  <svg xmlns="http://www.w3.org/2000/svg" xlink="http://www.w3.org/1999/xlink" width="50" height="50" viewBox="0 0 50 50">
    <defs>
      <circle id="cprefix__a" cx="25" cy="25" r="25" />
      <path id="cprefix__c" d="M31.2 18.652c-.502-.297-6.114-3.721-6.146-3.721L18.94 11.21v3.49c0 .428-.314.758-.721.758h-1.38c-2.634 0-5.017 1.12-6.773 2.963-1.599 1.68-2.634 3.985-2.79 6.52.501-.625 1.097-1.185 1.787-1.646 1.348-.922 2.947-1.482 4.672-1.482h4.452c.377 0 .722.33.722.758v3.49l12.291-7.41zm-5.425-5.006l7.15 4.347c.125.066.219.165.313.297.188.362.094.823-.25 1.02l-14.362 8.694c-.125.099-.25.132-.407.132-.408 0-.721-.33-.721-.758v-4.017h-3.732c-1.442 0-2.79.46-3.888 1.218-1.129.79-2.038 1.91-2.634 3.227-.094.297-.376.527-.69.527-.407 0-.72-.329-.72-.757v-2.042c0-3.161 1.254-6.059 3.229-8.166 2.007-2.075 4.735-3.392 7.776-3.392h.659v-4.05c0-.132.03-.264.094-.396.188-.362.627-.46.972-.263l7.211 4.38z" />
    </defs>
    <g fill="none" fillRule="evenodd">
      <mask id="cprefix__b" fill="#fff">
        <use href="#cprefix__a" />
      </mask>
      <g fill="#F2F2F2" mask="url(#cprefix__b)" opacity=".6">
        <path d="M0 0H50V50H0z" transform="rotate(-180 25 25)" />
      </g>
      <g mask="url(#cprefix__b)">
        <g transform="translate(5.833 5.833)">
          <mask id="cprefix__d" fill="#fff">
            <use href="#cprefix__c" />
          </mask>
          <use fill="#000" fillRule="nonzero" href="#cprefix__c" />
          <g fill="#000" mask="url(#cprefix__d)">
            <path d="M0 0H38.333V38.333H0z" transform="rotate(-180 19.167 19.167)" />
          </g>
        </g>
      </g>
    </g>
  </svg>

);

export default SocialShareSVG;
