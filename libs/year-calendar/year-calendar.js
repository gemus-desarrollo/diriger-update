/* 
 * Copyright 2017 
 * PhD. Geraudis Mustelier Portuondo
 * Este software esta protegido por la Ley de Derecho de Autor
 * Numero de Registro: 0415-01-2016
 */

    function divYearCalendar(year, minYear, maxYear) {
        var _year= year;
        var _divYearCalendar= $('.calendar-year-container');
        _divYearCalendar.find('.year-title').text(year)
        
        function clear() {     
            _divYearCalendar.find('.months-container').fadeOut(300).hide();
            _divYearCalendar.find('#calendar-year-' + _year).fadeIn(300);
            _divYearCalendar.find('#calendar-year-' + _year ).show(300);
        }
        
        clear();
        
        _divYearCalendar.find('.prev').click(function() {
            _divYearCalendar.find('.next').show();

            _year = parseInt(_divYearCalendar.find('.year-title').text());
            _year = _year <= minYear ? minYear : --_year;
            _divYearCalendar.find('.year-title').text(_year);
            
            if(_year == minYear) {
                $(this).hide();
            }
            clear();
        });

        _divYearCalendar.find('.next').click(function() {
            _divYearCalendar.find('.prev').show();

            _year = parseInt(_divYearCalendar.find('.year-title').text());
            _year = _year >= maxYear ? maxYear : ++_year;
            _divYearCalendar.find('.year-title').text(_year);   
            
             if(_year == maxYear) {
                $(this).hide();
            }           
            clear();
        });  
    }
