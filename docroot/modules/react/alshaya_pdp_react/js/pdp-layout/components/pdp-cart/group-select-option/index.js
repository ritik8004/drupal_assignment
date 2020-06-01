import React from 'react';

const GroupSelectOption = (props) => {

  console.log(props.configurables);
  const {
    groupSelect,
    handleSelectionChanged,
    configurables,
    showGroup,
    groupName,
    code,
    nextCode,
    nextValues
  } = props;

  return (
    <>
      <div className="group-anchor-wrapper">
        {Object.keys(configurables.alternates).map((alternate) => (
          <a href="#" onClick={(e) =>groupSelect(e, configurables.alternates[alternate])}>{configurables.alternates[alternate]}</a>
        ))}
      </div>
      <div className="group-option-wrapper">
        {(showGroup && groupName) ? (
          <select id={code} className="select-attribute-group clicked" onChange={(e) => handleSelectionChanged(e, code)}>
            {Object.keys().maconfigurables.valuesp((attr) => (
              <option
                value={attr}
                groupdata={JSON.stringify(configurables.values[attr])}
              >
                {configurables.values[attr][groupName]}
              </option>
            ))}
          </select>
        ) : (
        <>
          <select
            id={code}
            className="select-attribute-group"
            onChange={(e) => handleSelectionChanged(e, code)}
          >
            {Object.keys(configurables.values).map((attr) => {
              console.log(nextCode);
              console.log(nextValues);
              return (
                <option
                  value={attr}
                  groupdata={JSON.stringify(configurables.values[attr])}
                >
                  {configurables.values[attr][Object.keys(configurables.values[attr])[0]]}
                </option>
              );
            })}
          </select>
          </>
        )}

      </div>
    </>
  );
};
export default GroupSelectOption;
