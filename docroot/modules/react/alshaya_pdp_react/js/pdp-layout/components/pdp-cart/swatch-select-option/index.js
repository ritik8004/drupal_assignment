import React from 'react';

const SwatchSelectOption = ({ configurables, code }) => (
  <div className="non-groupped-attr">
    <ul id={code} className="select-attribute">
      {Object.keys(configurables.values).map((attr) => (
        <li key={attr}>
          <a href="#" style={{ backgroundImage: `url(${configurables.values[attr].swatch_image})` }} />
        </li>
      ))}
    </ul>
  </div>
);

export default SwatchSelectOption;
