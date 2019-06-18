// Run DatePicker
rbl_js.date_picker();

// Run Select 2
rbl_js.select2();

// Run
if (rbl_js.exist_tag("canvas[id=rbl-user-history-canvas]")) {

   let chart_data = JSON.parse(rbl_chart_history_log);

    // Prepare Chart Data
    let datasets = [];
    let i = 0;
    Object.keys(chart_data['label']).forEach(function (key) {
        let color = rbl_js.random_color(i);
        datasets.push({
            label: chart_data['label'][key],
            data: chart_data['data'][key],
            backgroundColor: 'rgba(' + color[0] + ',' + color[1] + ',' + color[2] + ',' + '0.3)',
            borderColor: 'rgba(' + color[0] + ',' + color[1] + ',' + color[2] + ',' + '1)',
            borderWidth: 1,
            fill: true
        });
        i++;
    });

    rbl_js.line_chart('rbl-user-history-canvas', chart_data['title'], chart_data['date'], datasets);
}