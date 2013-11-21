var page = require('webpage').create(),
    system = require('system'),
    fs = require('fs'),
    options = {};

//Add error handler
phantom.onError = function(msg, trace) {
    var msgStack = ['PHANTOM ERROR: ' + msg];
    if (trace && trace.length) {
        msgStack.push('TRACE:');
        trace.forEach(function(t) {
            msgStack.push(' -> ' + (t.file || t.sourceURL) + ': ' + t.line + (t.function ? ' (in function ' + t.function + ')' : ''));
        });
    }
    console.error(msgStack.join('\n'));
    phantom.exit(1);
};

//Collect arguments
options = JSON.parse(system.args[3]);
options.html_uri = system.args[1];
options.output = system.args[2];

if( options.header_html_path !== null ){
    if( !fs.exists(options.header_html_path) ){
        console.log("File for options.header_html_path not found at "+options.header_html_path);
        phantom.exit(1);
    }
    options.header_html = fs.read(options.header_html_path);
}
if( options.footer_html_path ){
    if( !fs.exists(options.footer_html_path) ){
        console.log("File for options.footer_html_path not found at "+options.header_html_path);
        phantom.exit(1);
    }
    options.footer_html = fs.read(options.footer_html_path);
}

page.paperSize = {
    margin: {
        top: options.margin_top,
        right: options.margin_right,
        bottom: options.margin_bottom,
        left: options.margin_left
    },
    header: {
        height: options.header_height,
        contents: phantom.callback(function(pageNum, numPages) {
            return options.header_html.replace(/{{page_number}}/gi,pageNum).replace(/{{total_pages}}/gi,numPages);
        })
    },
    footer: {
        height: options.footer_height,
        contents: phantom.callback(function(pageNum, numPages) {
            return options.footer_html.replace(/{{page_number}}/gi,pageNum).replace(/{{total_pages}}/gi,numPages);
        })
    }
}
if( options.format.indexOf("*") != -1 ){
    var size = option.format.split("*");
    page.width = size[0];
    page.height = size[1];
} else {
    page.paperSize.format = options.format;
    page.paperSize.orientation = options.orientation;
}

page.zoomFactor = (options.zoom?options.zoom:1);
page.open(options.html_uri, function(status){
    if( status !== "success" ){
        console.log('Unable to load the address!');
    } else {
        window.setTimeout(function(){
           page.render(options.output);
           phantom.exit();
        },options.wait_time);
    }
});