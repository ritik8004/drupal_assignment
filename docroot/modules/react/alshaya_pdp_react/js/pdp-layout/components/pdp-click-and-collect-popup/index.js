import React from 'react';
import parse from 'html-react-parser';

const ClickCollectContent = (props) => {
  const { store, location } = props;
  return (
    <>
      <a href="#" className="location">{location}</a>
      <div className="magv2-click-collect-results">
        {parse(store)}
      </div>
    </>
  );
};

export default ClickCollectContent;
