import React from 'react';

export default class Pagination extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
    };
    this.navigatePage = this.navigatePage.bind(this);
  }

  navigatePage = (buttonValue) => (e) => {
    e.preventDefault();
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
        <button type="button" value="prev" onClick={(e) => this.navigatePage(e.target.value)} disabled={prevButtonDisabled}>Previous Page</button>
        <span>
          {currentPage}
          /
        </span>
        <span>{numberOfPages}</span>
        <button type="button" value="next" onClick={(e) => this.navigatePage(e.target.value)} disabled={nextButtonDisabled}>Next Page</button>
      </div>
    );
  }
}
