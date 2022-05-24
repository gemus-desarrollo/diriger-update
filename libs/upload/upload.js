/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * Incorporar al html
 * 
 */
var _ROOT_DIR;

function InitUploaderImage(id, root, maxW, maxH) {
    _ROOT_DIR = decodeURIComponent(root) + 'libs\\upload\\';

    var str = "<input type='hidden' id='" + id + "-maxW' name='" + id + "-maxW' value='200' />" +
        "<input type='hidden' id='" + id + "-maxH' name='" + id + "-maxH' value='200' />" +
        "<input type='hidden' id='" + id + "-trash' name='" + id + "-trash' value='0' />"

    $('#' + id).append(str);

    maxW = maxW != 'undefined' && maxW > 0 ? maxW : 200;
    maxH = maxH != 'undefined' && maxH > 0 ? maxH : 200;

    $('#' + id + '-maxW').val(maxW);
    $('#' + id + '-maxH').val(maxH);

    // console.log($('#'+id+'.panel-file .img'));

    if ($('#' + id + '.panel-file .img').is(':empty')) {
        var imgElement = document.createElement("img");
        $(imgElement).prop('src', _ROOT_DIR + "img/add-image.png");

        $(imgElement).on('load', function() {
            window.URL.revokeObjectURL(imgElement.src);
            imgSize(id, $(imgElement));
        });

        append(id, imgElement);
    } else {
        imgSize(id, $('#' + id + ' .img img'));
        $('#' + id + '-trash').val(0);
    }

    $('#' + id + '-btn-trash').click(function() {
        confirm("Esta seguro de querrer eliminr esta imagen?", function(ok) {
            if (ok) {
                $('#' + id + '.panel-file .img').empty();
                $('#' + id + '-trash').val(1);
            } else {

            }
        });
    });

    $('#' + id + '-upload').change(function() {
        var image = this.files[0];
        emptyUpload(id);

        var imgElement = document.createElement("img");
        imgElement.src = window.URL.createObjectURL(image);

        if (!validate_image(id, image, imgElement))
            return;

        $(imgElement).on('load', function() {
            window.URL.revokeObjectURL(imgElement.src);
            imgSize(id, imgElement);
        });

        append(id, imgElement);
    });
}

function InitUploaderFile(id, root, maxSize = 5) {
    maxSize = parseInt(maxSize) > 0 ? maxSize : 5;
    _ROOT_DIR = decodeURIComponent(root) + 'libs\\upload\\';

    if ($('#' + id + '.panel-file .img').is(':empty')) {
        var imgElement = document.createElement("img");
        $(imgElement).prop('src', _ROOT_DIR + "img/add-file.png");

        $(imgElement).on('load', function() {
            window.URL.revokeObjectURL(imgElement.src);
            imgSize(id, $(imgElement));
        });

        append(id, imgElement);
    }

    $('#' + id + '-upload').change(function() {
        var file = this.files[0];
        emptyUpload(id);

        var imgElement = document.createElement("img");
        imgElement.src = imgIcon(file.type);

        if (!validate_file(file, maxSize))
            return;

        $(imgElement).on('load', function() {
            window.URL.revokeObjectURL(imgElement.src);
        });

        append(id, imgElement);

        $('#' + id + '-trash').val(0);
    });
}

/*************************************************************
 * format files uploads
 */
function imgSize(id, imgElement) {
    var maxW = $('#' + id + '-maxW').val();
    var maxH = $('#' + id + '-maxH').val();

    var w = $(imgElement).width();
    var h = $(imgElement).height();
    var r = w / h;

    if (w > maxW) {
        w = maxW;
        h = w / r;
    }
    if (h > maxH) {
        h = maxH;
        w = r * h;
    }

    if (w > maxW || h > maxH)
        imgSize(imgElement);

    $(imgElement).attr('width', Math.floor(w) + 'px');
    $(imgElement).attr('height', Math.floor(h) + 'px');
}

function imgIcon(type) {
    var icon;
    console.log(type);

    switch (type) {
        case 'application/msword':
            icon = 'docx_win.png';
            break;
        case 'application/pdf':
            icon = 'pdf.png';
            break;
        case 'application/vnd.ms-powerpoint':
            icon = 'pptx_win.png';
            break;
        case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
            icon = 'pptx_win.png';
            break;
        case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
            icon = 'docx_win.png';
            break;
        case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
            icon = 'xlsx_win.png';
            break;
        case 'application/vnd.ms-excel':
            icon = 'xlsx_win.png';
            break;
        case 'application/msaccess':
            icon = 'accdb.png';
            break;

        case 'application/gzip':
            icon = 'rar.png';
            break;

        case 'text/plain':
            icon = 'text.png';
            break;
        case 'text/html':
            icon = 'url.png';
            break;
        case 'message/rfc822':
            icon = 'text.png';
            break;

        case 'application/octet-stream':
            icon = 'settings.png';
            break;

        case 'application/x-shockwave-flash':
            icon = 'fla.png';
            break;

        case 'video/mp4':
            icon = 'avi.png';
            break;
        case 'video/avi':
            icon = 'avi.png';
            break;
        case 'video/x-flv':
            icon = 'mov.png';
            break;
        case 'video/3gpp':
            icon = 'avi.png';
            break;
        case 'video/x-ms-wmv':
            icon = 'avi.png';
            break;
        case 'video/mpeg':
            icon = 'mpeg.png';
            break;

        case 'audio/mpeg':
            icon = 'wma.png';
            break;

        case 'image/x-icon':
            icon = 'gif.png';
            break;
        case 'image/gif':
            icon = 'gif.png';
            break;
        case 'image/jpeg':
            icon = 'jpeg.png';
            break;
        case 'image/pjpeg':
            icon = 'jpeg.png';
            break;
        case 'image/jpg':
            icon = 'jpeg.png';
            break;
        case 'image/bmp':
            icon = 'bmp.png';
            break;
        case 'image/png':
            icon = 'png.png';
            break;
        case 'image/tiff':
            icon = 'tiff.png';
            break;

        default:
            icon = 'faq.png';
            break;
    }

    return _ROOT_DIR + 'img/' + icon;
}

function closeUploaderImage(id) {
    emptyUpload(id);
    var imgElement = document.createElement("img");
    $(imgElement).prop('src', _ROOT_DIR + "img/add-image.png");
    append(id, imgElement);

    $('#' + id + '-upload-init').val(0);
}

function closeUploaderFile(id) {
    emptyUpload(id);
    var imgElement = document.createElement("img");
    $(imgElement).prop('src', _ROOT_DIR + "img/add-file.png");
    append(id, imgElement);

    $('#' + id + '-upload-init').val(0);
}

function emptyUpload(id) {
    $('#' + id + ' .img').empty();
}

function append(id, imgElement) {
    $('#' + id + ' .img').append(imgElement);
}


/** *****************************************************************
 *  validate
 */
function validate_image(id, image, imgElement) {
    var error = '';

    if (image.size / 1048576 > 1)
        error = 'El archivo no puede superar los 1 Mb.';

    switch (image.type) {
        case 'image/png':
            break;
        case 'image/gif':
            break;
        case 'image/jpeg':
            break;
        case 'image/pjpeg':
            break;
        case 'image/jpg':
            break;
        case 'image/bmp':
            break;
        default:
            error = 'Formato de archivo no reconocido.';
    }

    var mW = $(imgElement).width();
    var mH = $(imgElement).height();

    if (mW > $('#' + id + '-maxW').val() || mH > $('#' + id + '-maxH').val())
        error = "Las dimensiones de la imagen, " + mW + " x " + mH + " p&iacute;xeles, no son mayores que las permitidas p&iacute;xeles.";

    if (error.length > 1) {
        alert(error);
        return false;
    }

    return true;
}

function validate_file(file, maxSize = 5) {
    maxSize = parseInt(maxSize) > 0 ? maxSize : 5;
    var error = '';

    if (file.size / 1048576 > maxSize)
        error = "El archivo no puede superar los " + maxSize + " Mb.";

    if (error.length > 1) {
        alert(error);
        return false;
    }

    return true;
}

function trash_upload() {

}