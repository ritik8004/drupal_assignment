import React from 'react';

const PdpCart = (props) => {
  const { configurableCombinations, skuCode } = props;
  const { configurables } = configurableCombinations[skuCode];
  return (
    <div className="pdp-cart-form">
      <form action="#" method="post">
        {Object.keys(configurables).map((key) => (
          <div>
            <label htmlFor={key}>{configurables[key].label}</label>
            <select id={key}>
              {Object.keys(configurables[key].values).map((attr) => (
                <option
                  value={configurables[key].values[attr].label}
                >
                  {configurables[key].values[attr].label}
                </option>
              ))}
            </select>
          </div>
        ))}
        <button type="submit" value="Add to basket">{Drupal.t('Add To Basket')}</button>
      </form>
    </div>
  );
};
export default PdpCart;
