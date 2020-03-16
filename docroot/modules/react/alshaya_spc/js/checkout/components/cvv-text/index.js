import React from 'react';

const CVVToolTipText = () => {
  return (
    <>
      <svg xmlns="http://www.w3.org/2000/svg" width="51" height="33" viewBox="0 0 51 33">
        <g fill="none" fillRule="evenodd">
          <rect width="51" height="33" fill="#DDD" rx="1.6"/>
          <path fill="#333" d="M0 5.634h51v8.049H0z"/>
          <text fill="#D0021B" fontFamily="ArialMT, Arial" fontSize="4.8">
            <tspan x="37.453" y="23.512">123</tspan>
          </text>
          <text fill="#333" fontFamily="ArialMT, Arial" fontSize="4.8">
            <tspan x="9.563" y="23.512">1234 5678</tspan>
          </text>
          <ellipse cx="41.8" cy="22.098" stroke="#D0021B" rx="5.94" ry="6"/>
        </g>
      </svg>
      <p>{Drupal.t('This code is a three or four digit number printed on the front or back of the credit card')}</p>
    </>
  );
};

export default CVVToolTipText;
