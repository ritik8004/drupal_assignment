import React from 'react';
import ConditionalView from "../../../js/utilities/components/conditional-view";

/**
 * Provides the egift card header.
 *
 * @param {*} egiftHeading
 * @param {*} egiftSubHeading
 */
export const egiftCardHeader = ({
  egiftHeading,
  egiftSubHeading,
}) => (
  <div className="egift-header-wrapper">
    <p>
      <strong>{egiftHeading}</strong>
    </p>
    <p>{egiftSubHeading}</p>
  </div>
);

/**
 * Provides different form element.
 *
 * @param {*} type
 * @param {*} name
 * @param {*} placeholder
 * @param {*} className
 * @param {*} buttonText
 */
export const egiftFormElement = ({
  type = '',
  name = '',
  placeholder = '',
  className = '',
  buttonText = '',
  changeHandler,
}) => {
  // Separate template based on type.
  let rtnTemplate = '';
  switch (type) {
    case 'submit':
      rtnTemplate = (
        <input
          className="egift-button"
          id={`egift-${name}`}
          type={type}
          value={Drupal.t(buttonText, {}, { context: 'egift' })}
        />
      );
      break;
    default:
      rtnTemplate = (
        <div className={`egift-type-${type}`}>
          <ConditionalView condition={changeHandler}>
            <input
              onChange={changeHandler}
              type={type}
              name={`egift_${name}`}
              placeholder={placeholder}
              className={className}
            />
            <div id={`egift_${name}_error`} className="error" />
          </ConditionalView>
          <ConditionalView condition={!changeHandler}>
            <input
              type={type}
              name={`egift_${name}`}
              placeholder={placeholder}
              className={className}
            />
            <div id={`egift_${name}_error`} className="error" />
          </ConditionalView>
        </div>
      );
  }

  return rtnTemplate;
};
