import React from 'react'
import ReactDOM from 'react-dom'

const CustomBundle = () => {
  return (
    <div> Coming from custom react .... </div>
  )
}

ReactDOM.render(
  <CustomBundle />,
  document.querySelector('#custom-bundle')
)
