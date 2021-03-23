import React from 'react';
import getStringMessage from '../../../../../../js/utilities/strings';

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
            {' '}
            {Drupal.t('of')}
            {' '}
            {totalReviews}
          </div>
          <ul className="filter-result">
            {currentOptions.map((item) => (
              <li key={item.value}>
                <button
                  type="button"
                  onClick={() => this.handleClick(item)}
                  className="bv-active-filter-button"
                >
                  {item.label}
                </button>
              </li>
            ))}
          </ul>
          <div className="clear-all-button">
            <button type="button" onClick={() => this.handleClick('clearall')}>{getStringMessage('clear_all')}</button>
          </div>
        </div>
      );
    }
    return (null);
  }
}
