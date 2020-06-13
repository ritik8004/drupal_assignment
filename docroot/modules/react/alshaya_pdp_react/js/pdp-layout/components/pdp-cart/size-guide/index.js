import React from 'react';
import parse from 'html-react-parser';

const SizeGuide = (props) => {
  const { sizeGuideLink } = props;

  return (
    <>
      <div className="size-guide">
        {parse(sizeGuideLink)}
      </div>
    </>
  );
};

export default SizeGuide;
