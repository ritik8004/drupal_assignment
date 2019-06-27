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

      $('#gps-data-select, #gps-screen-select').on('change', function () {
        urlObject = {
          url: $(this).value,
          data: [0,0,0,0,0,0,0]
        }

        let url = document.getElementById('gps-data-select').value;
        screen = document.getElementById('gps-screen-select').value;

        let urlTest = '/google-page-speed/' + screen + '?url=' + encodeURIComponent(url);

        jQuery.get(urlTest, function(data, status){
          if (status == 'success' && data) {
            let showData = jQuery.parseJSON(data);
            urlObject.data = showData;
            google.charts.load('current', {'packages':['annotatedtimeline']});
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
          colors: ['#a52714', '#097138', '#FF0000', '#FF7F00', '#FFFF00', '#00FF00', '#0000FF'],
          displayAnnotations: true,
          displayExactValues: true
        };

        var chart = new google.visualization.AnnotatedTimeLine(document.getElementById('chart_div1'));
        chart.draw(data, options);
      }
    }
  };

})(jQuery, Drupal);
