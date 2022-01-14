import React from 'react';
import { getAreaFieldKey, getDeliveryAreaStorage } from '../../../utilities/delivery_area_util';

const AvailableAreaItems = ({
  attr, value, parentId, isStandardDelivery,
  isSameDayDelivery, isExpressDelivery, handleLiClick,
}) => {
  const standardDeliveryClass = isStandardDelivery ? 'active' : 'disabled';
  const samedayDeliveryClass = isSameDayDelivery ? 'active' : 'disabled';
  const expressDeliveryClass = isExpressDelivery ? 'active' : 'disabled';
  const currentArea = getDeliveryAreaStorage();
  const areaFieldKey = getAreaFieldKey();
  let activeClass = 'in-active';
  if (currentArea !== null && areaFieldKey !== null) {
    if (parseInt(currentArea.value[areaFieldKey], 10) === parseInt(attr, 10)) {
      activeClass = 'active';
    }
  }
  return (
    <li
      value={attr}
      id={`value${attr}`}
      data-parent-id={parentId}
      className={`area-select-list-item ${activeClass}`}
    >
      <span onClick={(e) => handleLiClick(e)} data-area-id={attr} data-parent-id={parentId} data-label={value} className="area-select-item-wrapper">
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
