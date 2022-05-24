/* 
 * Copyright 2017 
 * PhD. Geraudis Mustelier Portuondo
 * Este software esta protegido por la Ley de Derecho de Autor
 * Numero de Registro: 0415-01-2016
 */

// JavaScript Document
var heightHeader = 0;
var _startX = 0; // mouse starting positions
var _startY = 0;
var _offsetX = 0; // current element offset
var _offsetY = 0;
var _dragElement = null; // needs to be passed from OnMouseDown to OnMouseMove
var _oldZIndex = 0; // we temporarily increase the z-index during drag
// var _debug = $('debug');    // makes life easier

var _dx = 0; // distance between mouse position and left of window
var _dy = 0;

function UpdateCursorPosition(e) {
    _startX = e.pageX;
    _startY = e.pageY;
}

function UpdateCursorPositionDocAll(e) {
    _startX = e.clientX;
    _startY = e.clientY;
}

function InitDragDrop() {
    _oldZIndex = 0;
    $('body').append('<div class="overlay-container"></div>');
    document.onmousedown = OnMouseDown;
    document.onmouseup = OnMouseUp;

    if (document.all)
        document.onmousemove = UpdateCursorPositionDocAll;
    else
        document.onmousemove = UpdateCursorPosition;
}

function displayDiv(width, height, left, top) {
    _oldZIndex = _dragElement.css('zIndex');
    if (width) {
        _dragElement.css('width', width + '%');
        _dragElement.css('minWidth', width + '%');
        _dragElement.css('maxWidth', width + '%');
    }

    if (height)
        _dragElement.css('maxHeight', height + '%');

    _dragElement.css('position', 'absolute');

    left = (left && left != 'undefined' && left < 100) ? screen.width * (left / 100) : screen.width / 4
    top = (top && top != 'undefined' && top < 100) ? screen.height * (top / 100) : screen.height / 4
    _dragElement.css('left', left + "px");
    _dragElement.css('top', top + "px");

    _startX = left;
    _startY = top;

    if (self.pageYOffset) {
        _offsetX = self.pageXOffset;
        _offsetY = self.pageYOffset;
    } else if (document.documentElement && document.documentElement.scrollTop) {
        _offsetX = document.documentElement.scrollLeft;
        _offsetY = document.documentElement.scrollTop;
    } else if (document.body) {
        _offsetX = document.body.scrollLeft;
        _offsetY = document.body.scrollTop;
    }
}

function displayModalDiv(divId, title, width, height, left, top) {
    _dx = 20;
    _dy = 50;
    _dragElement = $('#' + divId);

    $('.overlay-container').addClass('show');
    _dragElement.show(function() {
        try {
            if (title && title != 'undefined')
                _dragElement.find('.ajax-title').text(title);
        } catch (e) {; }
    });

    _dragElement.addClass('show');
    _dragElement.css('zIndex', _dragElement.css('zIndex') + 10);

    displayDiv(width, height, left, top);
}

function displayFloatingDiv(divId, title, width, height, left, top) {
    _dx = 50;
    _dy = 20;
    _dragElement = $('#' + divId);

    _dragElement.show(function() {
        try {
            if (title && title != 'undefined') {
                _dragElement.find('.ajax-title').each(function() {
                    $(this).text(title);
                });
            }
        } catch (e) {; }
    });

    displayDiv(width, height, left, top);

    try {
        if (title && title != 'undefined') {
            $('#' + divId + ' .ajax-title').html(title);
        }
    } catch (e) {; }
}

function AssignPosition(d) {
    _dragElement = $('#' + d);
    _dragElement.show();
    _dragElement.focus();

    _dx = 20;
    _dy = 68;

    if (self.pageYOffset) {
        _offsetX = self.pageXOffset;
        _offsetY = self.pageYOffset;
    } else if (document.documentElement && document.documentElement.scrollTop) {
        _offsetX = document.documentElement.scrollLeft;
        _offsetY = document.documentElement.scrollTop;
    } else if (document.body) {
        _offsetX = document.body.scrollLeft;
        _offsetY = document.body.scrollTop;
    }

    if (document.all) {
        _startX += _offsetX;
        _startY += _offsetY;
    }

    if (_startX + _dragElement.width() > $(window).width())
        _startX = $(window).width() - _dragElement.width() - _dx;
    else if (_startX - _dragElement.width() < 0)
        _startX = 2 * _dx;

    if ($(window).height() > _dragElement.height()) {
        if (_startY + _dragElement.height() > $(window).height())
            _startY = $(window).height() - _dragElement.height() - 5;
        else if (_startY - _dragElement.height() < heightHeader ? heightHeader + _dy : 5)
            _startY = heightHeader ? heightHeader + _dy : 5;
    }

    _dragElement.css('left', _startX + "px");
    _dragElement.css('top', _startY + "px");
}

function OnMouseDown(e) {
    // IE is retarded and doesn't pass the event object
    if (e == null) e = window.event;

    // IE uses srcElement, others use target
    var target = e.target != null ? e.target : e.srcElement;
    // for IE, left click == 1
    // for Firefox, left click == 0
    var _class = target.className;
    try {
        if (_class.indexOf('win-drag') == -1)
            return true;
    } catch (e) {
        return true;
    }

    var parent = $(e.target);
    var i = 0;
    do {
        parent = parent.parent();
        ++i;
    }
    while (parent.attr('data-bind') != 'draganddrop' && i < 8);
    if (i >= 8)
        return true;

    var pclass = parent.attr('class');
    var id = parent.attr('id');

    if (e.button == 1 && window.event != null || e.button == 0) {
        _dragElement = $('#' + id);

        // grab the mouse position
        _startX = e.clientX;
        _startY = e.clientY;

        // grab the clicked element's position
        _offsetX = ExtractNumber(target.style.left);
        _offsetY = ExtractNumber(target.style.top);

        _dx = _startX - _dragElement.position().left;
        _dy = _startY - _dragElement.position().top;

        // bring the clicked element to the front while it is being dragged
        _oldZIndex = _dragElement.css('zIndex');
        _dragElement.css('zIndex', 123456);

        // tell our code to start moving the element with the mouse
        document.onmousemove = OnMouseMove;
        // cancel out any text selections
        document.body.focus();
        // prevent text selection in IE
        document.onselectstart = function() {
            return true;
        };

        // prevent IE from trying to drag an image
        target.ondragstart = function() {
            return true;
        };

        // prevent text selection (except IE)
        return true;
    }
}

function OnMouseMove(e) {
    if (e == null) var e = window.event;
    // this is the actual "drag code"

    var y = 0,
        left = 0,
        sy = 0;
    var x = 0,
        top = 0,
        sx = 0;

    if (e.pageX || e.pageY) {
        sy = 0;
        y = e.pageY;
        sx = 0;
        x = e.pageX;
    } else {
        sy = document.body.scrollTop - document.body.clientTop;
        y = e.clientY - sy;
        sx = document.body.scrollLeft - document.body.clientLeft;
        x = e.clientX - sx;
    }

    if (top < heightHeader) top = heightHeader + 5;

    if (_dx == 0) _dx = 68;
    if (_dy == 0) _dy = 20;

    left = x - _dx;
    if ($(window).width() - x < (_dragElement.width() - _dx))
        left = $(window).width() - _dragElement.width() - 18;
    if (x < _dx)
        left = 18;

    top = y - _dy;
    if (y < (_dy + heightHeader))
        top = heightHeader + 8;

    if ($(window).height() > _dragElement.height()) {
        if (y > ($(window).height() - _dragElement.height()))
            top = $(window).height() - _dragElement.height() - 8;
    }

    _dragElement.css('top', top + 'px');
    _dragElement.css('left', left + 'px');

    //   _debug.innerHTML = '(' + _dragElement.style.left + ', ' +  _dragElement.style.top + ')';   
}

function OnMouseUp(e) {
    if (_dragElement != null) {
        if ($('.overlay-container').css('display') == 'none') _dragElement.css('zIndex', _oldZIndex);
        // we're done with these events until the next OnMouseDown

        document.onmousemove = null;
        document.onselectstart = null;
        _dragElement.ondragstart = null;
    }

    InitDragDrop();
}

function CloseWindow(id) {
    if (id.length < 1) {
        return true;
    }

    _dragElement = $('#' + id);
    $('.overlay-container').hasClass('show') ? _dragElement.removeClass('show') : _dragElement.hide();
    if ($('.overlay-container').hasClass('show'))
        $('.overlay-container').remove();

    try {
        _dragElement.hide();
    } catch (e) {; }

    _dragElement = null;
}

function HideContent(id) {
    if (id.length < 1) {
        return true;
    }

    _dragElement = $('#' + id);
    _dragElement.hide();
    _dragElement = null;
}

function ExtractNumber(value) {
    var n = parseInt(value);
    return n == null || isNaN(n) ? 0 : n;
}

// this is simply a shortcut for the eyes and fingers
/*
function $(id) {
    return document.getElementById(id);
}
*/