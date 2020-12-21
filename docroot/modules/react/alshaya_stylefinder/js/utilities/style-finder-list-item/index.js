import React from 'react';

const handleStep = (e, props) => {
  const { answer, counter, handleStepSubmit } = props;
  handleStepSubmit(e, answer.nid, answer.attrCode, answer.choice, counter);
};

const StyleFinderListItem = (props) => {
  const { answer } = props;
  return (
    <>
      {answer
      && (
        <li
          onClick={(e) => handleStep(
            e,
            props,
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
};

export default StyleFinderListItem;
