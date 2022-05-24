function rgb2hex(rgb) {
    if (rgb.search("rgb") == -1) {
        return rgb;
    } else {
        rgb = rgb.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+))?\)$/);

        function hex(x) {
            return ("0" + parseInt(x).toString(16)).slice(-2);
        }
        return hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
    }
}

var charts_color_pick;
var charts_color_pick_class;

function _charts_color_pick(colorElem) {
    charts_color_pick = rgb2hex($(colorElem).css('background-color'));
    charts_color_pick_class = 'bg-' + charts_color_pick;
    console.log(charts_color_pick_class);
}

$(document).ready(function() {
    $('.color-block div').click(function(e) {
        _charts_color_pick($(this));

        $('#color-color').html('#' + charts_color_pick);
        $('#color-class').removeClass();
        $('#color-class').addClass('col-4 mb-6 clearfix ' + charts_color_pick_class);
    });
});