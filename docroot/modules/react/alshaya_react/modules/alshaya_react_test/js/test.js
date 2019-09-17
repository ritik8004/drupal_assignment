import React from 'react'
import ReactDOM from 'react-dom'

class SimpleBundle extends React.Component {
  constructor() {
    super()
  }

  render() {
    return (
      <div>Coming from Simple bundle ....</div>
    )
  }
}

ReactDOM.render(
  <SimpleBundle />,
  document.querySelector('#simple-bundle')
)
