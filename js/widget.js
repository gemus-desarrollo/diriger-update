try {
    jQuery.extend(jQuery.easing, {
        easeOutCubic: function(x, t, b, c, d) {
            return c * ((t = t / d - 1) * t * t + 1) + b;
        }
    });
    jQuery(document).ready(function($) {
        var winHeight = $(window).height();
        var isopen = false,
            bitHeight = $('#bitsubscribe').height(),
            $bit = $('#bit');
        var height = bitHeight > winHeight ? (winHeight - 57) : bitHeight;
        setTimeout(function() {
            if (bitHeight > winHeight) {
                $bit.css('max-height', winHeight + 'px');
                $('#bitsubscribe').css('max-height', winHeight - 30 + 'px');
                $('#bitsubscribe').css('overflow', 'auto');
            }
            $bit.animate({ bottom: '-' + height - 30 + 'px' }, 200);
            if (document.location.href.indexOf('blogsub=') !== -1) {
                open();
            }
        }, 300);
        var open = function() {
            if (isopen)
                return;
            isopen = true;
            $('a.bsub', $bit).addClass('open');
            $('#bitsubscribe', $bit).addClass('open')
            $bit.stop();
            if (bitHeight > winHeight) {
                $bit.css('max-height', winHeight + 'px');
                $('#bitsubscribe').css('max-height', winHeight - 30 + 'px');
                $('#bitsubscribe').css('overflow', 'auto');
            }
            $bit.animate({ bottom: '0px' }, { duration: 400, easing: "easeOutCubic" });
        }
        var close = function() {
            if (!isopen)
                return;
            isopen = false;
            $bit.stop();

            $bit.animate({ bottom: '-' + height - 30 + 'px' }, 200, function() {
                $('a.bsub', $bit).removeClass('open');
                $('#bitsubscribe', $bit).removeClass('open');
            });
        }
        $('a.bsub', $bit).click(function() {
            if (!isopen)
                open();
            else
                close();
        });
        var target = $bit.has('form').length ? $bit : $(document);
        target.keyup(function(e) {
            if (27 == e.keyCode)
                close();
        });
    });
} catch (e) {;
}