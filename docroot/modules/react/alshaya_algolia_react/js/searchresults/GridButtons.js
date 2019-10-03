import React from 'react';

const GridButtons = (props) => (
  <div class="grid-buttons">
    <div class="large-col-grid" onClick={props.toggle}>
      <svg class="g2" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15">
        <g class="grid" fill="#DADADA" fill-rule="nonzero">
          <path d="M0 0h7v15H0zM8 0h7v15H8z"></path>
        </g>
      </svg>
      <svg class="g1" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15">
        <path class="grid" fill="#DADADA" fill-rule="nonzero" d="M0 0h15v15H0z"></path>
      </svg>
    </div>
    <div class="small-col-grid active" onClick={props.toggle}>
      <svg class="g3" xmlns="http://www.w3.org/2000/svg" width="14" height="15" viewBox="0 0 14 15">
        <g class="grid" fill="#DADADA" fill-rule="nonzero">
          <path d="M0 0h4v7H0zM5 0h4v7H5zM10 0h4v7h-4zM10 8h4v7h-4zM5 8h4v7H5zM0 8h4v7H0z"></path>
        </g>
      </svg>
      <svg class="g2" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15">
        <g class="grid" fill="#DADADA" fill-rule="nonzero">
          <path d="M0 0h7v15H0zM8 0h7v15H8z"></path>
        </g>
      </svg>
    </div>
  </div>
);

export default GridButtons;
