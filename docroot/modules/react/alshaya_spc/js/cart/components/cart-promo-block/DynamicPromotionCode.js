import React from 'react';
import { applyCode } from '../../../utilities/checkout_util';

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
