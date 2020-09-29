import React from 'react';

const LoyaltyClubBenefitsRow = (props) => {
  const {
    rowClass,
    iconType,
    rowLabel,
    cell1,
    cell2,
    cell3,
  } = props;

  const iconTypeClass = iconType === 'undefined' ? '' : iconType;

  if (iconType === 'stars') {
    return (
      <div className={`aura-loyalty-benefits-row ${rowClass} ${iconTypeClass}`}>
        <div className="row-item">{rowLabel}</div>
        <div className="row-item"><span className="star-icon" /></div>
        <div className="row-item">
          <span className="star-icon" />
          <span className="star-icon" />
        </div>
        <div className="row-item">
          <span className="star-icon" />
          <span className="star-icon" />
          <span className="star-icon" />
        </div>
      </div>
    );
  }

  if (iconType === 'tick23') {
    return (
      <div className={`aura-loyalty-benefits-row ${rowClass} ${iconTypeClass}`}>
        <div className="row-item">{rowLabel}</div>
        <div className="row-item"><span>-</span></div>
        <div className="row-item"><span className="tick-icon" /></div>
        <div className="row-item"><span className="tick-icon" /></div>
      </div>
    );
  }

  if (iconType === 'tick3') {
    return (
      <div className={`aura-loyalty-benefits-row ${rowClass} ${iconTypeClass}`}>
        <div className="row-item">{rowLabel}</div>
        <div className="row-item"><span>-</span></div>
        <div className="row-item"><span>-</span></div>
        <div className="row-item"><span className="tick-icon" /></div>
      </div>
    );
  }

  return (
    <div className={`aura-loyalty-benefits-row ${rowClass} ${iconTypeClass}`}>
      <div className="row-item">{rowLabel}</div>
      <div className="row-item">{cell1}</div>
      <div className="row-item">{cell2}</div>
      <div className="row-item">{cell3}</div>
    </div>
  );
};

export default LoyaltyClubBenefitsRow;
