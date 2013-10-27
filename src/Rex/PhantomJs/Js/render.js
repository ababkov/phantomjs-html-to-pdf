var page = require('webpage').create(),
    system = require('system'),
    options = {};

//arguments
options = JSON.parse(system.args[3]);
options.html_uri = system.args[1];
options.output = system.args[2];

page.paperSize = {
    format: options.format,
    margin: {
        top: options.margin_top,
        right: options.margin_right,
        bottom: options.margin_bottom,
        left: options.margin_left
    },
    header: {
        height: options.header_height,
        contents: phantom.callback(function(pageNum, numPages) {
            if (pageNum == 1) {
                return "";
            }
            return options.header_html;//"<h1>Header <span style='float:right'>" + pageNum + " / " + numPages + "</span></h1>";
        })
    },
    footer: {
        height: options.footer_height,
        contents: phantom.callback(function(pageNum, numPages) {
            if (pageNum == numPages) {
                return "";
            }
            return options.footer_html;//"<h1>Footer <span style='float:right'>" + pageNum + " / " + numPages + "</span></h1>";
        })
    }
}
page.open(options.html_uri, function(status){
    if( status !== "success" ){
        console.log('Unable to load the address!');
        phantom.exit();
    } else {
        window.setTimeout(function(){
           page.render(options.output);
           phantom.exit();
        },options.wait_time);
    }
});