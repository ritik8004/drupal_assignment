import React from 'react';

export default class StyleFinderListItem extends React.Component {
  constructor() {
    super();
    this.state = {};
  }

  handleStepSubmit = (e, answer, attrCode, choice, counter) => {
    const { handleStepSubmit } = this.props;
    handleStepSubmit(e, answer, attrCode, choice, counter);
  };

  render() {
    const { answer, counter } = this.props;

    return (
      <>
        {answer
          && (
          <li
            onClick={(e) => this.handleStepSubmit(
              e,
              answer.nid,
              answer.attrCode,
              answer.choice, counter,
            )}
            className="list-item style-finder-list-item"
          >
            <div className="style-finder-list-image">
              <img src={answer.image_url} />
            </div>
            <div className="style-finder-list-title">
              {answer.title}
            </div>
            <div className="style-finder-list-text">
              {answer.description}
            </div>
          </li>
          )}
      </>
    );
  }
}
