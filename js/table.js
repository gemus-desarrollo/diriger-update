// JavaScript Document

// MUSTELIER

/* This script and many more are available free online at
The JavaScript Source!! http://javascript.internet.com
Created by: Chris Heilmann :: http://www.alistapart.com/articles/tableruler */

var _this_tr = null;

function tableruler() {
    if (document.getElementById && document.createTextNode) {
        var tables = document.getElementsByTagName('table');
        for (var i = 0; i < tables.length; i++) {
            if (tables[i].id.search('tablewitdhrollover') > -1) {
                var trs = tables[i].getElementsByTagName('tr');

                for (var j = 0; j < trs.length; j++) {
                    if (trs[j].parentNode.nodeName == 'TBODY') {
                        trs[j].onclick = function() { if (_this_tr != null) _this_tr.className = 'tr-out';
                            this.className = 'tr-select';
                            _this_tr = this; }

                        trs[j].onmouseover = function() { if (this.className != 'tr-select') { this.className = 'tr-over'; return false } }
                        trs[j].onmouseout = function() { if (this.className != 'tr-select') { this.className = 'tr-out'; return false } }
                    }
                }
            }
        }
    }
}

window.onload = function() { tableruler(); }