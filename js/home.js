/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(function() {
    $('.app-body').height($(window).height() - 50);
    $(window).on('resize', function() {
        $('.app-body').height($(window).height() - 50);
    });

    $('.toggle').click(function() {
        $('.nav-list').toggleClass('active');
    });

    $(document).on('click', '#navbar li a, .nav-list li a, .navigation a', function(e) {
        var href = $(this).attr('href');
        if (href === '#' || /^http.*/.test(href)) {
            return;
        }
        e.preventDefault();
        $('.nav-list').removeClass('active');
        location.hash = href;
        $('iframe').attr('src', href);
        initNavigation(href);
    });

    var href = location.hash.substring(1) || 'background.php?=$csfr_token=123abc&';
    $('iframe').attr('src', href);
    initNavigation(href);

    $(window).on('blur', function() {
        $('.dropdown-toggle').parent().removeClass('open');
    });
});

function initNavigation(href) {
    var $el = $('a[href="' + href + '"]'),
        $prev, $next;

    $('.ribbon a').attr('href', 'background.php' + href + '&csfr_token=123abc');

    if (!$el.length) {
        return;
    }
    $prev = $el.parent().prev('li');
    $next = $el.parent().next('li');
    $('.navigation a').hide();

    if ($prev.text()) {
        $('.navigation .previous').show()
            .attr('href', $prev.find('a').attr('href'))
            .find('span').text($prev.text());
    }
    if ($next.text()) {
        $('.navigation .next').show()
            .attr('href', $next.find('a').attr('href'))
            .find('span').text($next.text());
    }
}