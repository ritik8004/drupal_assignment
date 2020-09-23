import React from 'react';

const AuraFormTextField = (props) => {
  const { name, placeholder } = props;

  return (
    <div className={`spc-aura-textfield ${name}-form-item`}>
      <input
        placeholder={placeholder}
        name={name}
        className={name}
        type="text"
      />
    </div>
  );
};

export default AuraFormTextField;
