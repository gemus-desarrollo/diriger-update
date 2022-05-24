/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Ncesita de la libreria jquery.bpopup.min.js
 * 
 */
var _idWindowsAlert = 0;
var _strDivWindowsAlert;


$(document).ready(function() {

    function _close(div) {
        $('#' + div).addClass('hidden');
        $('#' + div).remove();
        $('div.b-modal').hide();
    }

    window._originalAlert = window.alert;
    window.alert = function(text, event) {
        var div;

        bootStrapAlert = function() {
            div = 'windowAlertModal' + _idWindowsAlert;
            if ($('#' + div).length == 1)
                return true;

            _strDivWindowsAlert = '<div id="' + div + '" class="windowAlert has-error panel panel-default hidden"> \
                  <div class="panel-header">&nbsp;</div> \
                  <div class="panel-body">Body for Alert</div> \
                  <div class="panel-footer"> \
                      <button class="btn alert-btn-accept">Aceptar</button> \
                  </div> \
              </div>';

            $('body').append(_strDivWindowsAlert);
            $('#' + div + ' .alert-btn-accept').click(function() {
                _close(div);
                try {
                    event(true);
                } catch (e) {; }

            });
            ++_idWindowsAlert;
            return true;
        }

        if (bootStrapAlert()) {
            $('#' + div + ' > div.panel-body').text(text);
            $('#' + div).removeClass('hidden')
            $('#' + div).bPopup({
                modalClose: false,
                opacity: 0.3,
                positionStyle: 'fixed', //'fixed' or 'absolute'
                position: ['25%', '40%'] //x, y
            });
        } else {
            console.log('bootstrap was not found');
            window._originalAlert(text);
        }
    }

    window._originalConfirm = window.confirm;
    window.confirm = function(text, event) {
        var div;
        bootStrapConfirm = function() {
            div = 'windowAlertModal' + _idWindowsAlert;
            if ($('#' + div).length == 1)
                return true;

            _strDivWindowsAlert = '<div id="' + div + '" class="windowAlert has-error panel panel-default hidden"> \
                  <div class="panel-header">&nbsp;</div> \
                  <div class="panel-body">Body for Alert</div> \
                  <div class="panel-footer"> \
                      <button class="btn alert-btn-accept">Si</button> \
                      <button class="btn alert-btn-close">No</button> \
                  </div> \
              </div>';

            $('body').append(_strDivWindowsAlert);
            ++_idWindowsAlert;
            return true;
        }

        if (bootStrapConfirm()) {
            function unbind() {
                $('#' + div + ' .alert-btn-open').unbind('click', confirm);
                $('#' + div + ' .alert-btn-close').unbind('click', deny);
            }

            function accept() {
                _close(div);
                event(true);
            }

            function deny() {
                _close(div);
                event(false);
            }

            $('#' + div + ' .alert-btn-accept').bind('click', accept);
            $('#' + div + ' .alert-btn-close').bind('click', deny);

            $('#' + div + ' > div.panel-body').text(text);
            $('#' + div).removeClass('hidden');

            $('#' + div).bPopup({
                modalClose: false,
                opacity: 0.3,
                positionStyle: 'fixed', //'fixed' or 'absolute'
                position: ['25%', '40%'] //x, y  
            });
        } else {
            console.log('bootstrap was not found');
            window._originalConfirm(text);
        }
    }
});

function _alert(text) {
    alert(text, function(ok) {
        _alert(text);
    });
}