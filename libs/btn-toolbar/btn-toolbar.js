/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var toolbar_max_length;
var toolbar_length;
var toolbar_width;

function ExtractNumber(value) {
    var n = parseInt(value);
    return n == null || isNaN(n) ? 0 : n;
}

function set_toolbar(left) {
    if (left >= 0) {
        // $('.toolbar .btn-left .btn').css('visibility', 'hidden');
    } else {
        $('.toolbar .btn-left .btn').css('visibility', 'visible');
    }

    if (left <= -(50 + 2) * (toolbar_length - toolbar_max_length)) {
        // $('.toolbar .btn-right .btn').css('visibility', 'hidden');
    } else {
        $('.toolbar .btn-right .btn').css('visibility', 'visible');
    }
}

function InitBtnToolbar(id) {
    var n = $(window).width();
    n = Math.ceil(n / (50 + 2));
    toolbar_max_length = n - 4;

    if (id == 'undefined' || id == 0)
        id = 0;
    var left = id > toolbar_max_length ? (id - toolbar_max_length) * 50 : 0;

    leftpx = left > 0 ? -left + 'px' : '0px';
    $('.toolbar .toolbar-center .center-inside').css('margin-left', leftpx);

    toolbar_length = $('.toolbar .toolbar-center .center-inside a').length;
    left = $('.toolbar .toolbar-center .center-inside').css('margin-left');
    left = ExtractNumber(left);

    toolbar_width = $('.toolbar .toolbar-center').css('width');
    toolbar_width = ExtractNumber(toolbar_length);

    set_toolbar(left);

    $('.toolbar .toolbar-center .center-inside .btn').click(function() {
        $('.toolbar .toolbar-center .center-inside .btn').removeClass('active');
        $(this).addClass('active');
    });

    $('.toolbar .btn-left .double').click(function() {
        $('.toolbar .toolbar-center .center-inside').css('margin-left', '0px');

        set_toolbar(0);
    });

    $('.toolbar .btn-left .single').click(function() {
        if ($('.toolbar .btn-left .single').hasClass('disabled')) 
            return;

        var left = $('.toolbar .toolbar-center .center-inside').css('margin-left');
        left = parseInt(ExtractNumber(left));
        left += 50;
        $('.toolbar .toolbar-center .center-inside').css('margin-left', left + 'px');

        set_toolbar(left);
    });

    $('.toolbar .btn-right .double').click(function() {
        var left = -toolbar_length * 50 + 2 * toolbar_width + 240;
        $('.toolbar .toolbar-center .center-inside').css('margin-left', left + 'px');

        set_toolbar(left);
    });

    $('.toolbar .btn-right .single').click(function() {
        if ($('.toolbar .btn-right .single').hasClass('disabled')) 
            return;

        var left = $('.toolbar .toolbar-center .center-inside').css('margin-left');
        left = parseInt(ExtractNumber(left));
        left -= 50;
        $('.toolbar .toolbar-center .center-inside').css('margin-left', left + 'px');

        set_toolbar(left);
    });
}