function isMobile() {
    /*
    if (navigator.userAgent.match(/Android/i) ||
        navigator.userAgent.match(/webOS/i) ||
        navigator.userAgent.match(/iPhone/i) ||
        navigator.userAgent.match(/iPad/i) ||
        navigator.userAgent.match(/iPod/i) ||
        navigator.userAgent.match(/BlackBerry/i) ||
        navigator.userAgent.match(/Windows Phone/i))
        return true;
    else
        return false;
    */
    if (screen.width > 768)
        return false;
    if (screen.width <= 360)
        return "mobile";
    if (screen.width <= 768)
        return "table";
}

function setPcMenu() {
    $('.navd-content').removeClass('sidebar');
    $('.navd-content').addClass('topbar navbar navbar-expand-lg');
    $('.navd-content .dismiss').css('display', 'none');

    $('.navd-collapse').removeClass('list-unstyled components');
    $('.navd-collapse').addClass('navbar-nav mr-auto');

    $('.navd-container').addClass('container-fluid');
    $('.navd-menu').addClass('collapse navbar-collapse');

    $('.navd-header').removeClass('sidebar-header');
    $('.navd-header').addClass('navbar-brand');

    $('.navd-dropdown').addClass('dropdown');

    $('.navd-dropdown-menu').removeClass('collapse list-unstyled');
    $('.navd-dropdown-menu').addClass('dropdown-menu');

    $('.navd-end').addClass('ui-controlgroup-horizontal my-lg-0 ml-auto');
}

function setMobilMenu() {
    $('.navd-content').addClass('sidebar');
    $('.navd-content').removeClass('topbar navbar navbar-expand-lg');

    $('.navd-collapse').addClass('list-unstyled components');
    $('.navd-collapse').removeClass('navbar-nav mr-auto');

    $('.navd-container').removeClass('container-fluid');
    $('.navd-menu').removeClass('collapse navbar-collapse');

    $('.navd-header').removeClass('navbar-brand');

    $('.navd-dropdown').removeClass('dropdown');

    $('.navd-dropdown-menu').addClass('collapse list-unstyled');
    $('.navd-dropdown-menu').removeClass('dropdown-menu');

    $('.navd-end').removeClass('ui-controlgroup-horizontal my-lg-0 ml-auto');

    $('.navd-content .dismiss').css('display', 'block');
    $('.navd-header').addClass('sidebar-header');
}

function myIframeFunction() {
    if (!$('.sidebar').hasClass('active')) {
        $('.sidebar').addClass('active');
        $('.overlay').addClass('active');
        $('.collapse.in').toggleClass('in');
        $('a[aria-expanded=true]').attr('aria-expanded', 'false');
    } else {
        $('.sidebar').removeClass('active');
        $('.overlay').removeClass('active');
        $('.collapse.in').toggleClass('in');
    }
}

function set_child_y_scroll(child, maxHeight) {
    var posy = child.position().top;
    var height = child.height();
    var screenHeight = $(window).height();
    var overflow = height > (screenHeight - posy - 40) ? true : false;

    if (overflow) {
        child.height(screenHeight - posy - maxHeight);
        child.css('max-height', screenHeight - posy - maxHeight);
        child.css('overflow-y', 'scroll');
    }    
}

$(document).ready(function() {
    var maxHeigh = 0;
    if (isMobile()) {
        maxHeight = 400;
        setMobilMenu();
    } else {
        maxHeight = 120;
        setPcMenu();
    }  

    $.each(['show', 'hide'], function (i, ev) {
        var el = $.fn[ev];
        $.fn[ev] = function () {
          this.trigger(ev);
          return el.apply(this, arguments);
        };
      });

    //-- menu lateral ------------------------------------------------------------------
    $(".sidebar").mCustomScrollbar({
        theme: "minimal"
    });

    $('.dismiss, .overlay').on('click', function() {
        $('.sidebar').removeClass('active');
        $('.overlay').removeClass('active');
    });

    $('.dropdown-menu a.dropdown-toggle').on('click', function(e) {
        if (!$(this).next().hasClass('show')) {
            $(this).parents('.dropdown-menu').first().find('.show').removeClass("show");
        }
        var $subMenu = $(this).next(".dropdown-menu");
        $subMenu.toggleClass('show');

        $(this).parents('li.nav-item.dropdown.show').on('hidden.bs.dropdown', function(e) {
            $('.dropdown-submenu .show').removeClass("show");
        });

        return false;
    });

    $('li.navd-dropdown.date-menu').mouseover(function() {
        var child = $(this).find('ul.navd-dropdown-menu.date-menu');
        if (child.position()) {
            set_child_y_scroll(child, maxHeight);
        }        
    });

    $('li.navd-dropdown>a.dropdown-toggle').each(function() {
        var child = $(this).parent().find('ul.navd-dropdown-menu');
        if (child.position()) {
            set_child_y_scroll(child, maxHeight) 
        }
    });
});