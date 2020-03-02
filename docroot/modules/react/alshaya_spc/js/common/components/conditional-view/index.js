import React from 'react'

const ConditionalView = (props) => {
  if (props.condition === false) {
    return (null);
  }

  return (
    <React.Fragment>
      {props.children}
    </React.Fragment>
  )
}

export default ConditionalView;
