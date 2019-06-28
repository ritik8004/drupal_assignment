/**
 * @file
 * Javascript to draw google chart and show data.
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.showPageSpeedDataChart = {
    attach: function (context, settings) {
      let urlObject = {
        url: document.getElementById('gps-data-select').value,
        data: [0,0,0,0,0,0,0]
      }

      $('#gps-data-select, #gps-screen-select, #gps-time-select').on('change', function () {
        urlObject = {
          url: $(this).value,
          data: [0,0,0,0,0,0,0]
        }

        let url = document.getElementById('gps-data-select').value;
        let screen = document.getElementById('gps-screen-select').value;
        let time = document.getElementById('gps-time-select').value;

        let urlTest = '/google-page-speed/' + screen + '/' + time + '?url=' + encodeURIComponent(url);

        jQuery.get(urlTest, function(data, status){
          if (status == 'success' && data) {
            let showData = jQuery.parseJSON(data);
            urlObject.data = showData;
            google.charts.load('current', {'packages':['corechart','line']});
            google.charts.setOnLoadCallback(drawChart);
          }
        });
      })

      function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('datetime', 'Date');
        data.addColumn('number', 'First Contentful Paint');
        data.addColumn('number', 'First Meaningful Paint');
        data.addColumn('number', 'Speed Index');
        data.addColumn('number', 'First CPU Idle');
        data.addColumn('number', 'Interactive');
        data.addColumn('number', 'Maximum Potential First Input Delay');


        var element = '';

        urlObject.data.forEach(function (element) {
          element.forEach(function (item, index) {
            element[index] = element[index].replace(/[ms,]/g, '');
            element[index] = parseFloat(element[index])
          })
          element[0] = new Date(element[0]*1000);
          element[6] = parseFloat(element[6])/1000;

          data.addRows([
            element
          ]);
        })

        var options = {
          colors: ['#a52714', '#097138', '#FF0000', '#FF7F00', '#000000', '#00FF00'],
          hAxis: {
            title: 'Date and time'
          },
          vAxis: {
            title: 'Time(s)'
          },
          'width':'100%',
          'height':'auto',
          pointsVisible: true
        };

        var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
        chart.draw(data, options);
      }
    }
  };

})(jQuery, Drupal);
