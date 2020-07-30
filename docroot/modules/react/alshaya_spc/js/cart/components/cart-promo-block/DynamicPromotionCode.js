import React from 'react';

const applyCode = (e) => {
  const codeValue = e.target.innerHTML;
  if (codeValue !== undefined) {
    document.getElementById('promo-code').value = codeValue;
    document.getElementById('promo-action-button').click();
  }
};

const DynamicPromotionCode = (props) => {
  const { code, label } = props;

  if (code !== undefined && code !== null) {
    return (
      <div className="promotion-available-code">
        <div className="promotion-coupon-code available" onClick={(e) => applyCode(e)} data-coupon-code={code}>
          {code}
        </div>
        <span className="code-desc">{label}</span>
      </div>
    );
  }

  return '';
};

export default DynamicPromotionCode;
