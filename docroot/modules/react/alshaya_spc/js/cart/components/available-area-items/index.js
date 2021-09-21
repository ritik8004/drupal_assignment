import React from 'react';
import { getStorageInfo } from '../../../utilities/storage';

const AvailableAreaItems = ({
  attr, value, parentId, isStandardDelivery,
  isSameDayDelivery, isExpressDelivery, handleLiClick,
}) => {
  const standardDeliveryClass = isStandardDelivery ? 'active' : 'disabled';
  const samedayDeliveryClass = isSameDayDelivery ? 'active' : 'disabled';
  const expressDeliveryClass = isExpressDelivery ? 'active' : 'disabled';
  const currentArea = getStorageInfo('deliveryinfo-areadata');
  let activeClass = 'in-active';
  if (currentArea !== null) {
    if (parseInt(currentArea.value.area, 10) === attr) {
      activeClass = 'active';
    }
  }
  return (
    <li
      key={attr}
      value={attr}
      id={`value${attr}`}
      data-parent-id={parentId}
      className={`area-select-list-item ${activeClass}`}
    >
      <span onClick={(e) => handleLiClick(e)} className="area-select-item-wrapper">
        <div className="area-select-list-container">
          <div className="area-select-name">{value}</div>
          <div className="area-delect-delivery-type">
            <span className={`area-select-standard-delivery ${standardDeliveryClass}`}>{isStandardDelivery}</span>
            <span className={`area-select-sameday-delivery ${samedayDeliveryClass}`}>{isSameDayDelivery}</span>
            <span className={`area-select-express-delivery ${expressDeliveryClass}`}>{isExpressDelivery}</span>
          </div>
        </div>
      </span>
    </li>
  );
};

export default AvailableAreaItems;
