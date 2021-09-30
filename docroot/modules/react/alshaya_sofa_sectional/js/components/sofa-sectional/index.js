import React from 'react';

const SofaSectionalForm = (props) => {
  const { sku } = props;

  return (
    <>
      <div className="sofa-sectional-form-container">
        { sku }
      </div>
    </>
  );
};

export default SofaSectionalForm;
