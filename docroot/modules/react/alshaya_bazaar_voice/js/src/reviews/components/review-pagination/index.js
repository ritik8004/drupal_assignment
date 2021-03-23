import React from 'react';
import smoothScrollTo from '../../../utilities/smoothScroll';
import getStringMessage from '../../../../../../js/utilities/strings';

export default class Pagination extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
    };
    this.navigatePage = this.navigatePage.bind(this);
  }

  componentDidMount() {
    document.addEventListener('handlePaginationComplete', this.handlePaginationComplete);
  }

  componentWillUnmount() {
    document.removeEventListener('handlePaginationComplete', this.handlePaginationComplete, false);
  }

  handlePaginationComplete = (event) => {
    smoothScrollTo(event, '#review-summary-wrapper');
  }

  navigatePage = (buttonValue) => {
    const event = new CustomEvent('handlePagination', {
      bubbles: true,
      detail: {
        buttonValue,
      },
    });
    document.dispatchEvent(event);
  }

  render() {
    const {
      prevButtonDisabled,
      nextButtonDisabled,
      currentPage,
      numberOfPages,
    } = this.props;
    return (
      <div className="review-pagination">
        <div className="prev" onClick={(e) => this.navigatePage(e.target.value)}>
          <button type="button" value="prev" className="prev-btn" disabled={prevButtonDisabled}>{getStringMessage('previous_page')}</button>
        </div>
        <span>{`${currentPage}/${numberOfPages}`}</span>
        <div className="next" onClick={(e) => this.navigatePage(e.target.value)}>
          <button type="button" value="next" className="next-btn" disabled={nextButtonDisabled}>{getStringMessage('next_page')}</button>
        </div>
      </div>
    );
  }
}
