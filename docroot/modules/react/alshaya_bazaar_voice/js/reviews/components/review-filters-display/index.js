import React from 'react';

export default class ReviewFiltersDisplay extends React.Component {
  handleClick = (selectedOption) => {
    const {
      processingCallback,
    } = this.props;

    if (selectedOption.value !== 'none') {
      // Callback to process to remove option.
      processingCallback(selectedOption);
    }
  }

  render() {
    const {
      currentOptions,
      totalReviews,
      currentTotal,
    } = this.props;

    if (currentOptions.length > 0) {
      return (
        <div className="review-filter-display-wrapper">
          <div className="review-count">
            {currentTotal}
            {Drupal.t('of')}
            {totalReviews}
          </div>
          <ul>
            {currentOptions.map((item) => (
              <li key={item.value}>
                <button
                  type="button"
                  onClick={() => this.handleClick(item)}
                  className="bv-active-filter-button"
                >
                  {item.label}
                  <span className="bv-close-icon"> ✘ </span>
                </button>
              </li>
            ))}
          </ul>
          <button type="button" onClick={() => this.handleClick('clearall')}>{Drupal.t('Clear all')}</button>
        </div>
      );
    }
    return (null);
  }
}
