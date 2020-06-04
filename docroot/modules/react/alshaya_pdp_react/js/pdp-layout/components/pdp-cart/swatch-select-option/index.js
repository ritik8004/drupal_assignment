import React from 'react';

const SwatchSelectOption = (props) => {
  const {
    handleSelectionChanged,
    configurables,
    code,
    nextCode,
    nextValues,
  } = props;

  return (
    <>
      <div className="non-groupped-attr">
        <select id={code} className="select-attribute" onChange={(e) => handleSelectionChanged(e, code)}>
          {Object.keys(configurables.values).map((attr) => {
            if (code === nextCode) {
              if (nextValues.indexOf(attr) !== -1) {
                return (
                  <option
                    value={configurables.values[attr].value_id}
                    key={attr}
                  >
                    {configurables.values[attr].swatch_image}
                  </option>
                );
              }
              return (
                <option
                  value={configurables.values[attr].value_id}
                  key={attr}
                  disabled
                >
                  {configurables.values[attr].swatch_image}
                </option>
              );
            }
            return (
              <option
                value={configurables.values[attr].value_id}
                key={attr}
              >
                {configurables.values[attr].swatch_image}
              </option>
            );
          })}
        </select>
      </div>
    </>
  );
};
export default SwatchSelectOption;
