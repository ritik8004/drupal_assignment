import React from 'react';

class IncreaseDecrease extends React.Component {
  /**
   * Onclick handler for the plus/minus buttons.
   */
  handleOnClick = (e, action) => {
    e.persist();
    const { onClickCallback } = this.props;

    // Call parent component callback to perform actions.
    onClickCallback(e, action);
  };

  render() {
    const {
      qty, qtyText, isEnabledDecreaseBtn, isEnabledIncreaseBtn,
    } = this.props;

    return (
      <div className="qty-sel-container">
        <button type="submit" className="qty-sel-btn qty-sel-btn--down" onClick={(e) => this.handleOnClick(e, 'decrease')} disabled={!isEnabledDecreaseBtn} />
        <div className="qty-text-wrapper">
          <div className="qty">
            {qty}
          </div>
          { qtyText && (
            <div className="qty-text">
              {qtyText}
            </div>
          )}
        </div>
        <button type="submit" className="qty-sel-btn qty-sel-btn--up" onClick={(e) => this.handleOnClick(e, 'increase')} disabled={!isEnabledIncreaseBtn} />
      </div>
    );
  }
}

export default IncreaseDecrease;
