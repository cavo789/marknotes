/**
 * jQuery-inputpicker - A jQuery input picker plugin. It supports showing multiple columns select in input.
 * Copyright (c) 2017 Ukalpa - https://ukalpa.com/inputpicker
 * License: MIT
 */

(function(factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD. Register as an anonymous module.
        define(['jquery'], factory);
    } else if (typeof exports === 'object') {
        // Node/CommonJS
        factory(require('jquery'));
    } else {
        // Browser globals
        factory(jQuery);
    }
})(function ($) {

    var methods;
    methods = {
        init: function (options) {
            return this.each(function () {
                var uuid = _generateUid();
                var original = $(this);
                var input;   // Shadow input
                // Check if has been initiated
                // if (original.hasClass('inputpicker-original')) {
                //     input = _i(original);
                // }
                // else
                if (original.hasClass('inputpicker-original') && _getInputpickerDiv(_i(original))) {
                    var orginial_css = original.data('inputpicker-original-css');
                    //inputpicker-overflow-hidden
                      _getInputpickerDiv(_i(original)).closest('.inputpicker-overflow-hidden').remove();
                    original.removeClass(function (index, className) {
                        return (className.match (/\binputpicker-\S+/g) || []).join(' ');
                    });
                    original.css({
                        top:orginial_css['top'],
                        position: orginial_css['position']
                    });
                }
                {
                    original.data('inputpicker-uuid', uuid);
                    var ow = original.outerWidth();

                    // Clone input
                    var input = original.clone();
                    var input_disabled = input.prop('disabled');

                    // Initiate input
                    input.val('').data('inputpicker-uuid', uuid).addClass('inputpicker-input').prop('id', 'inputpicker-' + uuid).prop('name', 'inputpicker-' + uuid);

                    // Inputpicker div ( wrap fake input and arrow )
                    var inputpicker_div = $("<div id=\"inputpicker-div-" + uuid + "\" class=\"inputpicker-div\" data-uuid=\"" + uuid + "\" style=\"position:relative;overflow:auto;height:100%;\">" + "<span class=\"inputpicker-arrow\" data-uuid=\"" + uuid + "\" onclick=\"$(this).parent().find('input').inputpicker('toggle');event.stopPropagation();\" style=\"" + ( input_disabled ? "display:none;" : "") + "\"  ><b></b></span>" + "</div>")
                        .append(input)
                        .on('click', function (e) {

                            $(this).find('input').focus();

                            // input.is(":focus")
                            // var input = $(this).find('input');
                            // dd(input.is(":focus"));

                            // input.inputpicker('toggle');

                            e.stopPropagation();
                            e.preventDefault();
                        })
                        .attr('unselectable', 'on')
                        .css('user-select', 'none')
                        .on('selectstart', false);
                    ;

                    // Set width, do not put into the top html in terms of using responsive later.
                    inputpicker_div.css('width', ow + 'px');

                    var div_overflow_hidden = $("<div class=\"inputpicker-overflow-hidden\" style='overflow: hidden;'></div>");
                    original.after(div_overflow_hidden.append(inputpicker_div));

                    // Add Classes to the original element
                    original
                        .addClass('inputpicker-original')
                        .addClass('inputpicker-original-' + uuid)
                        .attr('tabindex', -1)
                        .data('inputpicker-input', input)
                        .data('inputpicker-original-css', {
                            'top': original.css('top'),
                            'position': original.css('position')
                        })
                        .css({
                            'position': 'fixed',
                            'top': '-1000px'
                        });

                    input.data('inputpicker-original', original)
                        .prop('autocomplete', 'off');


                    $.fn.inputpicker.elements.push(input);


                    // Start events handlers

                    // Original events
                    original.on('focus.inputpicker', function () {
                        var original = $(this);
                        var input = _i(original);
                        input.trigger('focus');

                    }).on('change.inputpicker', function () {
                        var original = $(this);
                        var input = _i(original);
                        _setValue(input, original.val());
                    }).on('disable.inputpicker', function () {
                        var original = $(this);
                        var input = _i(original);
                        _disable(input, true);
                    }).on('enable.inputpicker', function () {
                        var original = $(this);
                        var input = _i(original);
                        _disable(input, false);
                    })
                    ;

                    // input Events
                    input.on('focus.inputpicker', _eventFocus)
                        .on('blur.inputpicker', _eventBlur)
                        .on('keydown.inputpicker', _eventKeyDown)
                        .on('keyup.inputpicker', _eventKeyUp);

                    // End events handlers

                }

                // Start loading Settings -----------------------
                // Pick up settings from data attributes
                var _options = [];
                for (var k in $.fn.inputpicker.defaults) {
                    if (input.data(k)) {
                        _options[k] = input.data(k);
                    }
                }

                // Merge settings by orders: 1.options > 2 attr > 3.defaults
                // If option is array, set it as data
                var settings = $.extend({cache: {}}, $.fn.inputpicker.defaults, _options, Array.isArray(options) ? {data: options} : options);

                // Set default value for fieldText
                if (!settings['fieldText']) settings['fieldText'] = settings['fieldValue'];

                // Set default value for fields
                if (!settings['fields'].length) {
                    settings['fields'].push(settings['fieldText']);
                }

                // input.data('inputpicker-settings', settings);
                _set(input, settings);

                // End loading settings -------------------------


                // -------- Load & Show ------------------------------------

                _init(input);




                // Load data and set value
                _loadData(input, settings['data'], function (input) {
                    if (!_setValue(input, original.val())) {
                    }

                });
            })
        },

        /**
         * Load data manually when having changed url or params
         * It may trigger change if the value was changed
         * @param func - callback
         */
        loadData: function (data, func) {
            return this.each(function () {
                var input = _i($(this));
                _loadData.call(input,
                    input, data, function (input, data) {
                        if (_setValue(input, _o(input).val())) {
                            _o(input).trigger('change');
                        }
                        else {
                            // No change
                        }

                        if (typeof func == 'function') {
                            func.call(input, input, data);
                        }
                    });

            });
        },



        destroy: function (options) {
            return this.each(function () {
                /*

                  original
                        .addClass('inputpicker-original')
                        .addClass('inputpicker-original-' + uuid)
                        .attr('tabindex', -1)
                        .data('inputpicker-input', input)
                        .data('inputpicker-original-css', {
                            'top': original.css('top'),
                            'position': original.css('position')
                        })
                        .css({
                            'position': 'fixed',
                            'top': '-1000px'
                        });
                 */
                var original = _o($(this));
                var input = _i(original);
                var wrapped_list = _getWrappedList(input);
                var uuid = _uuid(input);
                input.removeClass('inputpicker-input');
                input.removeData('inputpicker-settings');
                input.remove();
                wrapped_list.remove();

                var original_css = original.data('inputpicker-original-css');
                original.css('top', original_css['top']).css('position', original_css['position']);
                original.removeData('inputpicker-original-css');
                original.prop('tabindex', null);
                original.removeClass('inputpicker-original-' + uuid);
                original.off('focus.inputpicker');
                original.off('change.inputpicker');
            })
        },

        /**
         * Get / Set options
         * @param k
         * @param v
         * @returns {*}
         */
        set: function (k, v) {
            var input = _i($(this));
            if (typeof k == 'undefined' && typeof v == 'undefined') {
                return _set(input);
            }
            if (typeof v == 'undefined') {
                return _set(input, k);
            }
            else {
                return _set(input, k, v);
            }
        },

        /**
         * Get / Set data
         * @param data
         * @returns {*}
         */
        data: function (data) {
            if (typeof data == 'undefined') {
                return _set(_i($(this)), 'data');
            }
            else {
                return this.each(function () {
                    var input = _i($(this));
                    _set(input, 'data', _formatData(_set(input, 'fieldValue'), data));
                });
            }
        },

        /*
        // Replace as new function to support selecting by specific field
        element: function (value, field) {
            return this.each(function () {
                var original = _o($(this));
                var input = _i(original);
                if (typeof value == 'undefined'){
                    value = original.val();
                }
                var fieldValue = (typeof field === 'undefined') ? _set(input, 'fieldValue') : field;

                // _set(input, 'data', _formatData(_set(input, 'fieldValue'), data));
                dd('fieldValue:' + fieldValue);

                var data = _set(input, 'data');
                if ( !data.length)  return null;   // No data
                var index_i = -1;
                for(var i = 0; i < data.length; i++){
                    if ( data[i][ fieldValue ] == value){
                        dd(["what:", data[i][ fieldValue ], value]);
                        dd(data[i]);
                        return data[i];
                    }
                }
                dd("return null");
                return null;

            });
        },
        */
        element: function (value, field) {
            var original = _o($(this));
            var input = _i(original);
            if (typeof value == 'undefined'){
                value = original.val();
            }
            var fieldValue = (typeof field === 'undefined') ? _set(input, 'fieldValue') : field;

            var data = _set(input, 'data');
            if ( !data.length)  return null;   // No data
            var index_i = -1;
            for(var i = 0; i < data.length; i++){
                if ( data[i][ fieldValue ] == value){
                    return data[i];
                }
            }
            return null;

        },

        /**
         * Show or hide the input
         */
        toggle: function (e) {
            return this.each(function () {
                var input = _i($(this));
                if (_isWrappedListVisible(input)) {
                    methods.hide.call(input, e);
                    dd('_isWrappedListVisible, so methods.hide.call');
                }
                else {
                    if (input.is(":focus")) {    // has focus, only need to show
                        methods.show.call(input, e);
                        dd('input.is focus');
                    }
                    else {   // not focused yet, check if need show after focus
                        dd('input.focus');
                        input.focus();
                        if (!_set(input, 'autoOpen')) {  // not autoOpen, need to open manually
                            methods.show.call(input, e);
                        }
                    }
                }
            });
        },

        /**
         * Show the input
         * @param e
         * @returns {*}
         */
        show: function (e) {
            return this.each(function () {
                var input = _i($(this));
                var uuid = _uuid(input);

                // Check the input is visible
                if (!_isInputVisible(input)) {
                    _alert('input[name=' + _name(input) + '] is not visible.');
                    return;
                }
                else if (_uuid(_getWrappedList()) == uuid) {
                    dd('_getWrappedList().show()');
                    _getWrappedList().show();
                }
                else {
                    dd('_render');
                    _getWrappedList(input).show();
                    _dataRender(input);
                }
            });
        },

        hide: function (e) {
            _hideWrappedList();
            // return this.each(function () {
            //     var input = $(this);
            // });
        },

        // deprecated by prop('disabled', true);
        // disable: function (v) {
        //     return this.each(function () {
        //         var input = _i($(this));
        //         var uuid = _uuid(input);
        //         _disable(input, v);
        //     });
        //
        // },

        // resize: function(w, h){
        //     return this.each(function () {
        //         var original = _o($(this));
        //         var input = _i(original);
        //         var reset_flg = false;
        //         dd([w, h]);
        //         if (typeof w != 'undefined'){
        //             reset_flg = true;
        //             input.width(w);
        //             _set(input, 'width', w.toString());
        //         }
        //         if (typeof h != 'undefined'){
        //             reset_flg = true;
        //             _set(input, 'height', h.toString());
        //         }
        //         // _setWrappedListWidthAndHeight(input);
        //         _dataRender(input);
        //
        //     });
        // },


        /**
         * Change the value and trigger change
         * @param value
         */
        val: function (value) {
            return this.each(function () {
                var original = _o($(this));
                var input = _i(original);
                if (_setValue(input, value)) {
                    original.trigger('change');
                }
                else{
                }
            });
        },

        /**
         * Check if has initiated
         */
        is: function () {
            var input = _i($(this));
            dd(input);
        },


        /**
         * Remove
         * @param v
         */
        removeValue: function (v) {
            return this.each(function () {
                var original = _o($(this));
                var input = _i(original);
                var value = _o(input).val();
                var delimiter = _set(input, 'delimiter');
                // if (_setValue(input, value)) {
                //     original.trigger('change');
                // }
                var arr_value = value ? value.toString().split(delimiter) : [];
                var i = _inArray( v, arr_value);

                if ( i > -1)   // Need to remove it
                {
                    arr_value.splice(i, 1);
                    _setValue(input, arr_value.join(delimiter));
                    _hideWrappedList(true);
                }
            });
        },

        // /**
        //  * Add change function
        //  * @param func
        //  */
        // change: function (func) {
        //     return this.each(function () {
        //         var original = $(this);
        //         original.on('change', func);
        //     });
        // },

        jumpToPage: function (page) {
            //
            return this.each(function () {
                var original = _o($(this));
                var input = _i(original);
                var value = _o(input).val();

                _set(input, 'pageCurrent', page ? parseInt(page) : 1);
                _loadData.call(input,
                    input, null, function (input, data) {
                        _dataRender(input);
                    });



            });
        },

        debug: true
    };

    // ----------------------------------------------------------------
    function dd() {
        if(methods.debug) {
            var args = Array.prototype.slice.call(arguments);
            console.log(args.length == 1 ? args[0] : args);
        }
    }

    // function _debug(input){
    //     if(methods.debug){
    //         var args = Array.prototype.slice.call(arguments);
    //         var pre = 'inputpicker(' + _uuid(input);
    //         if(_o(input))   {
    //             pre += '--' + _o(input).attr('name');
    //         }
    //         if(arguments.callee.caller.name){
    //             pre += '--' +  arguments.callee.caller.name;
    //         }
    //         args.unshift( pre + ')');
    //         dd(args);
    //     }
    // }

    function _name(input) {
        return _o(input).attr('name');
    }

    function _error(msg, input) {
        if(typeof input != 'undefined'){
            var original = _o(input);
            if(original){
                if(original.attr('name')){
                    msg += " for input[name=" + original.attr('name') + "]";
                }
                else if(original.attr('id')){
                    msg += " for input[id=" + original.attr('id') + "]";
                }
            }
        }
        throw msg + " in inputpicker.js";
    }

    function _alert(msg, input) {
        if (typeof input != 'undefined'){
            if(_name(input)){
                msg += ' input[name=' + _name(input) + ']';
            }
        }
        alert(msg);
    }

    // Check data and format it
    function _formatData(fieldValue, data) {
        if(!Array.isArray(data)){
            return [];
        }
        if (data.length && typeof data[0] != 'object') {
            var new_data = [];
            for (var i in data) {
                var o = {};
                o[fieldValue] = data[i];
                new_data.push(o);
            }
            data = new_data;
            new_data = null;
        }
        return data;
    }

    // Load remote json data
    function _execJSON(input, param, func, errorFunc) {
        var url = _set(input, 'url');

        if(typeof param == 'undefined' && typeof func == 'undefined'){
            _alert('The param is incorrect. input[name=' + _name(input) + ']');
            return;
        }
        if (typeof func == 'undefined' && typeof param == 'function'){
            func = param;
            param = {};
        }
        if (typeof func != 'function'){
            _alert('The callback function is incorrect. input[name=' + _name(input) + ']');
            return;
        }

        if (typeof errorFunc != 'function'){
            errorFunc = function(ret){
                alert(ret);
            }
        }

        // pagination: true,   // false: no
        //     pageMode: '',  // '' or 'scroll'
        //     pageField: 'p',
        //     pageLimitField: 'per_page',
        //     pageLimit: 10,
        //     pageCurrent: 1,
        if (_pagination(input)){
            // dd("param:" );
            // dd(param);
            // dd( "_set(input, 'pageField'):" + _set(input, 'pageField'));
            // dd( "_set(input, 'pageCurrent'):" + _set(input, 'pageCurrent'));
            param[_set(input, 'pageField')]
                = _set(input, 'pageCurrent');
            param[_set(input, 'pageLimitField')]
                = _set(input, 'limit') ? _set(input, 'limit') : 10;
        }

        param = $.extend({
            q: input.val(),
            limit : _set(input, 'limit'),
            fieldValue: _set(input, 'fieldValue'),
            fieldText: _set(input, 'fieldText'),
            value: _o(input).val()
        }, _set(input, 'urlParam'), param);

        var param_serialised = 'urlParams|' + $.param(param);


        var cacheData = _cache(input, param_serialised);
        if(_set(input, 'urlCache') ){
            if( typeof cacheData == 'undefined' ){
                dd('Set cache:' + _name(input));
                // $.get(url, param, function (ret) {
                //     _cache(input, param_serialised, ret);
                //     func(ret);
                // }, "json");

                $.ajax({
                    url : url,
                    data: param,
                    type: "GET",
                    dataType: 'json',
                    headers: _set(input, 'urlHeaders'),
                    success:function (ret) {
                        _cache(input, param_serialised, ret);
                        func(ret);
                    },
                    error: errorFunc
                })

            }
            else{
                func(cacheData);
                dd('Use cache');
            }
        }
        else{
            dd('Not use cache');
            // $.get(url, param, func, "json");
            $.ajax({
                url : url,
                data: param,
                type: "GET",
                dataType: 'json',
                headers: _set(input, 'urlHeaders'),
                success: func,
                error: errorFunc
            })



        }

    }


    // -----------------------------------------------------------------------------------

    /**
     * Extra initiate if necessary
     * @param input
     * @private
     */
    function _init(input) {

        // Set
        if ( _typeIsMultiple(input)) {
            _initMultiple(input);
        }
        else if( _typeIsTag(input)) {
            _initTag(input);
        }

        if (_set(input, 'responsive')){
            var inputpicker_div = _getInputpickerDiv(input);
            // inputpicker_div.css('width', _set(input, 'width'));
            $(window).bind('resize', function(){
                _setWrappedListWidthAndHeight(input);
                _setWrappedListPosition(input);

            });
        }




    }

    function _initMultiple(input) {
        var inputpicker_div = _getInputpickerDiv(input);
        if (!inputpicker_div.find('.inputpicker-multiple').length){
            var inputpicker_multiple_div = $("<div class=\"inputpicker-multiple\" style=\"\"><ul class=\"\">" +
                // "<li class=\"inputpicker-element\">Text 1 <a href=\"#\">x</a></li>" +
                "</ul></div>").prependTo(inputpicker_div);

            inputpicker_div
                .attr('class', inputpicker_div.attr('class') + ' ' + input.attr('class'))
                .css('border', input.css('border'))
                .addClass('inputpicker-multiple')
                .css('left', '0px')
                // To measure the length of input
                .append("<span class=\"input-span\" style=\"display: none;\" ></span>")
            ;


            input.removeClass().width(1)
                .on('keyup.inputpicker.multiple', function (e) {
                    var input = $(this);
                    var inputpicker_div = _getInputpickerDiv(input);
                    var span = inputpicker_div.find('.input-span');

                    span.text(input.val());
                    input.width(span.width() + 15);
                    _setWrappedListPosition(input);
                });

            input.detach().appendTo($("<li class=\"inputpicker-multiple-input\"></li>").appendTo(inputpicker_multiple_div.find('ul')));

        }

    }

    function _initTag(input) {
        var inputpicker_div = _getInputpickerDiv(input);
        if (!inputpicker_div.find('.inputpicker-multiple').length){
            var inputpicker_multiple_div = $("<div class=\"inputpicker-multiple\" style=\"\"><ul class=\"\"></ul></div>").prependTo(inputpicker_div);

            inputpicker_div
                .attr('class', inputpicker_div.attr('class') + ' ' + input.attr('class'))
                .css('border', input.css('border'))
                .addClass('inputpicker-multiple')
                .css('left', '0px')
                // To measure the length of input
                .append("<span class=\"input-span\" style=\"display: none;\" ></span>")
            ;


            input.removeClass().width(1)
                .on('keyup.inputpicker.multiple', function (e) {
                    var input = $(this);
                    var inputpicker_div = _getInputpickerDiv(input);
                    var span = inputpicker_div.find('.input-span');

                    span.text(input.val());
                    input.width(span.width() + 15);
                    _setWrappedListPosition(input);
                });

            input.detach().appendTo($("<li class=\"inputpicker-multiple-input\"></li>").appendTo(inputpicker_multiple_div.find('ul')));

        }

    }



    function _getInputpickerDiv(input) {
        return _i(input).closest('.inputpicker-div');
        // return _i(input).parent();
    }

    // function _getInputMultipleList(input) {
    //     var input_div = _getInputpickerDiv(input);
    //     if (!input_div.find('.inputpicker-multiple-list').length){
    //         input_div.prepend("<div class=\"inputpicker-multiple-list\" style='position1: absolute;left:0;top:0;'>" +
    //             "<ul class=\"\">" +
    //             // <li class="inputpicker-element"></li>
    //             "<li class=\"inputpicker-element\">Text 1</li>" +
    //             "<li class=\"inputpicker-element\">Text 2</li>" +
    //             "</ul></div>");
    //     }
    // }

    /**
     *
     * @param input
     * @param isSelected - true: selected false: unselected, undefined: all
     * @returns {*}
     * @private
     */
    function _getInputMultipleElements(input) {
        var p = '.inputpicker-element';
        return _getInputpickerDiv(input).find(p);
    }

    /**
     * Get wrapped list
     * If div wrapped list doest not exist, initiate it
     * If input is not undefined, init it.
     * @returns {*|null}
     * @private
     */
    function _getWrappedList(input) {
        // if ( !$.fn.inputpicker.wrapped_list){
        //     $.fn.inputpicker.wrapped_list = $('<div />', {
        //         id: 'inputpicker-wrapped-list',
        //         tabindex: -1,
        //
        //     }).css({
        //         'display':'none',
        //         'position': 'absolute',
        //         'overflow' : 'all'
        //     }).addClass('inputpicker-wrapped-list').data('inputpicker-uuid', 0).appendTo(document.body);
        //
        //     $(document).on('click', function (e) {
        //         if(!($(e.target).hasClass('inputpicker-input'))){
        //             _hideWrappedList();
        //         }
        //     });
        //
        //     $.fn.inputpicker.wrapped_list.on('click', function (e) {
        //         e.preventDefault();
        //         e.stopPropagation();
        //     });
        // }
        //
        // if (typeof input != 'undefined'){
        //     // Reset setting for wrapped list
        //     if ( $.fn.inputpicker.wrapped_list.data('inputpicker-uuid') != input.data('inputpicker-uuid')){
        //         $.fn.inputpicker.wrapped_list.data('inputpicker-uuid', _uuid(input)).html("");
        //     }
        // }
        // return $.fn.inputpicker.wrapped_list;

        var wrapped_list = $('#inputpicker-wrapped-list');
        if ( ! wrapped_list.length){
            wrapped_list = $('<div />', {
                id: 'inputpicker-wrapped-list',
                tabindex: -1
            }).css({
                'display':'none',
                'position': 'absolute',
                'overflow' : 'auto'
            }).addClass('inputpicker-wrapped-list')
                .data('inputpicker-uuid', 0)
                .appendTo(document.body);

            $(document).on('click', function (e) {
                if(!($(e.target).hasClass('inputpicker-input'))){
                    _hideWrappedList();
                }
            });

            wrapped_list.on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
            });
        }

        if (typeof input != 'undefined'){
            // Reset setting for wrapped list
            if ( _uuid(wrapped_list) != _uuid(input)){
                _uuid(wrapped_list.html(""), _uuid(input));
            }
        }
        return wrapped_list;

    }


    function _setWrappedListWidthAndHeight(input) {
        var inputpicker_div = _getInputpickerDiv(input);
        var wrapped_list = _getWrappedList();
        var setWidth = _set(input, 'width');
        var width, height;

        if ( setWidth.substr(-1) == '%'){
            var p = parseInt(setWidth.slice(0, -1));
            width = parseInt(p * inputpicker_div.outerWidth() / 100);

            // dd("p:" + p + "; width:" + width);
        }
        else{
            width = setWidth ? setWidth : inputpicker_div.outerWidth();
        }
        height = _set(input, 'height');

        // Change the list position
        wrapped_list.css({
            width: width,
            maxHeight: height,
            overflowY:'auto'
        });
    }


    function _setWrappedListPosition(input) {
        var inputpicker_div = _getInputpickerDiv(input);
        var left = inputpicker_div.offset().left , //+ input.outerWidth()
            top = inputpicker_div.offset().top + inputpicker_div.outerHeight();

        _getWrappedList(input).css({
            left: inputpicker_div.offset().left + 'px',
            top: ( inputpicker_div.offset().top + inputpicker_div.outerHeight() ) + 'px'
        });
    }

    /**
     *
     * @param input
     * @param offset
     * @private
     */
    function _changeWrappedListSelected(input, offset) {
        var wrapped_list = _getWrappedList();
        var wrapped_elements = _getWrappedListElements();
        // check if wrapped_list
        if ( _isWrappedListVisible(input) && wrapped_elements.length){
            // Move to the first if not selected

            // Select first if not any selected
            if ( wrapped_elements.length && _getWrappedListElements(true).length == 0){
                wrapped_elements.first().addClass('inputpicker-active');
            }
            else{
                var tr_active = _getWrappedListElements(true);
                if (offset){    // Change active if necessary
                    if ( offset < 0 && tr_active.prev().length ){
                        tr_active.removeClass('inputpicker-active').prev().addClass('inputpicker-active');
                        if (tr_active.prev().position().top < tr_active.outerHeight()) {
                            wrapped_list.scrollTop(wrapped_list.scrollTop() - tr_active.outerHeight());
                        }
                    }
                    else if (  offset > 0 && tr_active.next().length) {
                        tr_active.removeClass('inputpicker-active').next().addClass('inputpicker-active');
                        if ( ( tr_active.next().position().top + 2 * tr_active.outerHeight()) > wrapped_list.outerHeight()) {
                            wrapped_list.scrollTop(wrapped_list.scrollTop() + tr_active.outerHeight());
                        }


                    }



                }
            }



            // // Check and change the cursor position if necessary
            // if ( tr_active.length && ( ( tr_active.position().top + 2 * tr_active.outerHeight()) > wrapped_list.outerHeight()) ) {
            //     wrapped_list.scrollTop(wrapped_list.scrollTop() + tr_active.data('i') * tr_active.outerHeight());
            // }

        }


    }

    // Get the original input
    function _o(input) {
        return input.data('inputpicker-original') ? input.data('inputpicker-original') : input;
    }

    // Get the target input
    function _i(original) {
        return original.data('inputpicker-input') ? original.data('inputpicker-input') : original ;
    }

    function _uuid(o, uuid) {
        return typeof uuid == 'undefined' ? o.data('inputpicker-uuid') : o.data('inputpicker-uuid', uuid);

    }

    function _cache(input, name, value) {
        var settings = input.data('inputpicker-settings') ;
        if (typeof value == 'undefined'){
            if (typeof name == 'undefined'){    // get all settings
                return settings['cache'];
            }
            else if( typeof name == 'object'){  // set all settings
                settings['cache'] = name;
                input.data('inputpicker-settings', settings);
            }
            else{
                return settings['cache'][name];  // get setting
            }
        }
        else{
            settings['cache'][name] = value;
            input.data('inputpicker-settings', settings);
            return input;
        }
    }

    function _type(input) {
        var t = '';
        if (_set(input, 'multiple')){
            t = 'multiple';
        }
        else if (_set(input, 'tag')){
            t = 'tag';
        }
        return t;
    }

    function _typeIsMultiple(input) {
        return 'multiple' === _type(input);
    }

    function _typeIsTag(input) {
        return 'tag' === _type(input);
    }

    function _pagination(input) {
        var t = '';
        if(_set(input, 'pagination')){
            t = _set(input, 'pageMode');
            if (t != 'scroll')  t = 'tradition';
        }
        return t;
    }

    function _isCreatable(input) {
        return _set(input, 'creatable');
    }

    function _selectMode(input){
        return _set(input, 'selectMode');
    }

    function _selectModeIsRestore(input) { return _set(input, 'selectMode') == 'restore'; }
    function _selectModeIsActive(input) { return _set(input, 'selectMode') == 'active'; }
    function _selectModeIsCreatable(input) { return _set(input, 'selectMode') == 'creatable'; }
    function _selectModeIsEmpty(input) { return _set(input, 'selectMode') == 'empty'; }

    function _inputValueEqualToOriginalValue(input) {
        dd("_inputValueEqualToOriginalValue: _i(" + _i(input).val() + ") == _o(" + _o(input).val() + ")" );
        return _i(input).data('value') == _o(input).val();
    }

    function _disable(input, v) {
        _o(input).prop('disabled', v);
        input.prop('disabled', v);
        var input_div = _getInputpickerDiv(input);
        if ( v ){
            input_div.find('.inputpicker-arrow').hide();
        }
        else{
            input_div.find('.inputpicker-arrow').show();
        }
    }


    /**
     * Get / Set settings
     * Do not use it to set data, use _loadData
     * @param input
     * @param name
     * @param value
     * @returns {*}
     * @private
     */
    function _set(input, name, value) {
        var settings = input.data('inputpicker-settings') ;
        if (typeof value == 'undefined'){
            if (typeof name == 'undefined'){    // get all settings
                return settings;
            }
            else if( typeof name == 'object'){  // set all settings
                input.data('inputpicker-settings', name);
            }
            else{
                return settings[name];  // get setting
            }
        }
        else{   // set setting
            // if (name == 'data'){ // special check for "data"
            //     if( value = _formatData(input, value) ){
            //         settings[name] = value;
            //     }
            //     else{
            //         // Nothing to do
            //     }
            // }
            // else{
            settings[name] = value;
            // }
            input.data('inputpicker-settings', settings);
            return input;
        }
    }

    function _filterData(input) {
        var fields = _set(input, 'fields');
        var fieldValue = _set(input, 'fieldValue');
        var filterType =  _set(input, 'filterType');
        var filterField =  _set(input, 'filterField');
        var data = _formatData(fieldValue, methods.data.call(input));
        var input_value = input.val();
        var input_value_low = input_value.toString().toLowerCase();

        // var limit = _set(input, 'limit');
        // var page = ( typeof page == 'undefined' || page < 1 ) ? 1 : parseInt(page);
        // dd(data, limit, page);

        if (!_set(input, 'filterOpen') || !input_value_low || _set(input, 'url') || !_isArray(data) ){
            return data;
        }

        var new_data = [];
        var isShown;
        for(var i = 0; i < data.length; i++){
            isShown = false;
            if (typeof filterField == 'string' && filterField) // Search specific field
            {
                if (typeof data[i][filterField] == 'undefined'){
                    continue;
                }

                var fieldValue = data[i][filterField].toString().toLowerCase();
                if (filterType == 'start' && fieldValue.substr(0, input_value_low.length) == input_value_low) {
                    isShown = true;
                }
                else if (fieldValue.indexOf(input_value_low) != -1) {
                    isShown = true;
                }
            }
            else {
                if (typeof filterField != 'array' && typeof filterField != 'object') {
                    filterField = [];
                    for(var k in fields)    filterField.push(typeof fields[k] == 'object' ? fields[k]['name'] : fields[k] );
                }


                for (var k in filterField) {

                    if (typeof data[i][filterField[k]] == 'undefined'){
                        continue;
                    }

                    var fieldValue = data[i][filterField[k]].toString().toLowerCase();
                    if (filterType == 'start' && fieldValue.substr(0, input_value_low.length) == input_value_low) {
                        isShown = true;
                    }
                    else if (fieldValue.indexOf(input_value_low) != -1) {
                        isShown = true;
                    }
                }
            }

            if(isShown) new_data.push(data[i]);
        }
        return new_data;
    }

    /**
     * Render list data
     * @param input
     * @private
     */
    function _dataRender(input) {
        if (_typeIsMultiple(input)){
            _dataRenderMultiple(input);
        }
        else if (_typeIsTag(input)){
            _dataRenderTag(input);
        }
        else{
            _dataRenderDefault(input);
        }
    }

    function _renderCss(input) {

        var output = "";
        var tmp, tmp1, tmp2;
        var wrapped_list = _getWrappedList(input);

        // Load CSS
        output += "<style>";
        if ( tmp = _set(input, 'listBackgroundColor') ){
            wrapped_list.css('backgroundColor', tmp);
        }
        if ( tmp = _set(input, 'listBorderColor') ){
            wrapped_list.css('borderColor', tmp);
        }

        tmp1 = _set(input, 'rowSelectedBackgroundColor');
        tmp2 = _set(input, 'rowSelectedFontColor')
        if ( tmp1 || tmp2 ){
            output += ".inputpicker-wrapped-list .inputpicker-active{ ";
            if(tmp1)    output += "background-color: " + tmp1 + "; ";
            if(tmp2)    output += "color: " + tmp2 + "; ";
            output += "}";
        }
        output += "</style>";

        return output;
    }

    function _renderTableHeader(input) {
        var output = "";
        var fields = _set(input, 'fields');
        if(_set(input, 'headShow')){
            output += '<thead><tr>';
            for(var i = 0; i < fields.length; i++){
                var text = '';
                if (typeof fields[i] == 'object'){
                    text = fields[i]['text'] ? fields[i]['text'] : fields[i]['name'];
                }
                else{
                    text = fields[i];
                }
                output += '<th>' + text + '</th>';
            }
            output += '</thead>';
        }
        return output;
    }

    function _renderPaginationFooter(input){
        var fields = _set(input, 'fields');
        var output = "";
        if( fields.length > 0 && _pagination(input)){
            var fields = _set(input, 'fields');
            output += '<tfoot class="inputpicker-pagination"><tr><td align="right" colspan="' + fields.length + '">';
            output += "<div style=\"width:100%;\">";

            var count = _set(input, 'pageCount') ? parseInt(_set(input, 'pageCount')) : 0;
            var current_page = _set(input, 'pageCurrent') ? parseInt(_set(input, 'pageCurrent')) : 1;
            var limit = _set(input, 'limit') ? parseInt(_set(input, 'limit')) : 10;
            var last_page = Math.ceil( count / limit);
            var prev_page = current_page > 1 ? (current_page - 1) : 1;
            var next_page = current_page < last_page ? (current_page + 1) : last_page;

            output += "<div style=\"float:left;padding-left:5px;\">There are " + count + " results.</div>";

            output += "<div style=\"float:right;padding-right:5px;\">" +
                "<a href=\"javascript:void(0);\" onclick=\"$('#inputpicker-" + _uuid(input) + "').inputpicker('jumpToPage', '1');\">First</a>" +
                "<a href=\"javascript:void(0);\" onclick=\"$('#inputpicker-" + _uuid(input) + "').inputpicker('jumpToPage', '" + prev_page + "');\">Prev</a>" +
                "<span class=\"current-page\">" + current_page + "</span>" +
                "<a href=\"javascript:void(0);\" onclick=\"$('#inputpicker-" + _uuid(input) + "').inputpicker('jumpToPage', '" + next_page + "');\">Next</a>" +
                "<a href=\"javascript:void(0);\" onclick=\"$('#inputpicker-" + _uuid(input) + "').inputpicker('jumpToPage', '" + last_page + "');\">Last</a>" +
                "</div>";




            output += "</div></div>";
            output += '</td></tr></tfoot>';

        }
        return output;

    }

    /**
     * Draw multiple values
     */
    function _dataRenderTag(input) {
        var wrapped_list = _getWrappedList(input);

        _set(input, 'headShow', false);

        _dataRenderMultiple(input);

        // Add "Tag"
        //inputpicker-no-result
        // var tr_no_result =
        // if ( )
        dd('inputpicker-no-result', wrapped_list.find('.inputpicker-no-result').length);


    }

    /**
     * Draw multiple values
     */
    function _dataRenderMultiple(input){

        var inputpicker_div = _getInputpickerDiv(input);
        var wrapped_list = _getWrappedList(input);
        var uuid = _uuid(input);
        // var data = _set(input, 'data');
        var data = _filterData(input);
        var fields = _set(input, 'fields');
        var fieldValue = _set(input, 'fieldValue');
        var filterOpen = _set(input, 'filterOpen');
        var filterType = _set(input, 'filterType');
        var filterField = _set(input, 'filterField');
        var delimiter = _set(input, 'delimiter');
        var value = _o(input).val();
        var arr_value = value.toString().split(delimiter);
        var output = "";

        wrapped_list.show().data('inputpicker-uuid', uuid).html('');
        _setWrappedListWidthAndHeight(input);
        _setWrappedListPosition(input);

        // Load CSS
        output += _renderCss(input);

        // Draw table
        output += "<table class=\"table small\">" ;

        // Show head
        output += _renderTableHeader(input);

        // Show data
        if(data.length){
            output += "<tbody>";
            var isSelected = false;
            for(var i = 0; i < data.length; i++) {
                isSelected =  -1 < _inArray( data[i][fieldValue] , arr_value );
                output += '<tr class="inputpicker-element inputpicker-element-' + i + ' ' + (isSelected ? 'inputpicker-selected' : '') + ' " data-i="' + i + '" data-value="' + data[i][fieldValue] + '">';
                for (var j = 0; j < fields.length; j++) {

                    var k = (typeof fields[j] == 'object') ? fields[j]['name'] : fields[j];
                    var text = typeof data[i][k] != 'undefined' ? data[i][k] : '';

                    // Check if value is empty and set it is shown value
                    if (!text){
                        text = '&nbsp;';
                    }

                    output += '<td';
                    var html_style = "";
                    if(_isObject(fields[j])){
                        if(fields[j]['width']){
                            html_style += "width:" + fields[j]['width'];
                        }
                        // ...
                    }
                    output += ' style="' + html_style + '" ';
                    output += '>' + text + '</td>';
                }
                output += '</tr>';
            }

            output += "</tbody>";
        }
        else{
            output += "<thead><tr><td colspan='" + fields.length + "' align='center' class=\"inputpicker-no-result\">No results.</td></thead></tr>";
        }


        output += "</table>";

        wrapped_list.append($(output));

        // Set events
        wrapped_list.find('tbody').find('tr').each(function () {
            var that = $(this);
            that.on('mouseover', function (e) {
                wrapped_list.find('.inputpicker-element').each(function () {
                    $(this).removeClass('inputpicker-active');    //.removeClass('active')
                });
                that.addClass('inputpicker-active');
            }).on('click', function (e) {
                var self = $(this);
                var uuid = $('#inputpicker-wrapped-list').data('inputpicker-uuid') ;
                var input = $('#inputpicker-' + uuid);
                var selected = self.hasClass('inputpicker-selected');
                var delimiter = _set(input, 'delimiter');

                var value = _o(input).val();
                var arr_value = value ? value.toString().split(delimiter) : [];

                var i = _inArray( self.data('value'), arr_value);

                if (selected && i > -1)   // Need to remove it
                {
                    arr_value.splice(i, 1);
                    self.removeClass('inputpicker-selected');

                }
                else if( !selected ){   // Not selected, need to select
                    arr_value.push(self.data('value'));
                    self.addClass('inputpicker-selected');
                }

                _setValue(input, arr_value.join(delimiter));
                self.removeClass('inputpicker-active');

                input.val('');
                input.data('value', '');

                // _setValue(input, data[i][ _set(input, 'fieldValue') ]);
                // _o(input).trigger('change');

                // To fix the bug of unable to close automatically in IE.
                if (!_isMSIE()){
                    input.focus();
                }
                _hideWrappedList();

            })
        });

    }


    /**
     * Draw the results list
     * @param input
     * @private
     */
    function _dataRenderDefault(input) {
        var inputpicker_div = _getInputpickerDiv(input);
        var wrapped_list = _getWrappedList(input);
        var uuid = _uuid(input);
        // var data = _set(input, 'data');
        var data = _filterData(input);
        var fields = _set(input, 'fields');
        var fieldValue = _set(input, 'fieldValue');
        var filterOpen = _set(input, 'filterOpen');
        var filterType = _set(input, 'filterType');
        var filterField = _set(input, 'filterField');
        var highlightResult = _set(input, 'highlightResult');
        var value = _o(input).val();
        var input_keyword_low = _i(input).val().toString().toLowerCase();
        var output = "";
        // var tmp, tmp1, tmp2;
        // var setWidth = _set(input, 'width');
        // var width, height;
        //
        // if ( setWidth.substr(-1) == '%'){
        //     var p = parseInt(setWidth.slice(0, -1));
        //     width = parseInt(100 * inputpicker_div.outerWidth() / p);
        // }
        // else{
        //     width = setWidth ? setWidth : inputpicker_div.outerWidth();
        // }
        // height = _set(input, 'height');
        //
        // // Change the list position
        // wrapped_list.css({
        //     width: width,
        //     maxHeight: height,
        //     display: '',
        // })
        wrapped_list.show()
        .data('inputpicker-uuid', uuid).html('');

        _setWrappedListWidthAndHeight(input);
        _setWrappedListPosition(input);

        // Load CSS
        output += _renderCss(input);
        // output += "<style>";
        // if ( tmp = _set(input, 'listBackgroundColor') ){
        //     wrapped_list.css('backgroundColor', tmp);
        // }
        // if ( tmp = _set(input, 'listBorderColor') ){
        //     wrapped_list.css('borderColor', tmp);
        // }
        //
        // tmp1 = _set(input, 'rowSelectedBackgroundColor');
        // tmp2 = _set(input, 'rowSelectedFontColor')
        // if ( tmp1 || tmp2 ){
        //     output += ".inputpicker-wrapped-list .table .selected{ ";
        //     if(tmp1)    output += "background-color: " + tmp1 + "; ";
        //     if(tmp2)    output += "color: " + tmp2 + "; ";
        //     output += "}";
        // }
        // output += "</style>";

        // Draw table
        output += "<table class=\"table small\">" ;

        // Show head
        output += _renderTableHeader(input);

        // Show data
        if(data.length){
            output += "<tbody>";
            for(var i = 0; i < data.length; i++) {

                var tr_highlight = false;

                var output_tds = "";
                for (var j = 0; j < fields.length; j++) {

                    var k = (typeof fields[j] == 'object') ? fields[j]['name'] : fields[j];
                    var text = typeof data[i][k] != 'undefined' ? data[i][k] : '';

                    if ( value && text.toString().toLowerCase().indexOf(input_keyword_low) != -1){
                        tr_highlight = true;
                    }

                    // Check if value is empty and set it is shown value
                    if (!text){
                        text = '&nbsp;';
                    }

                    output_tds += '<td';
                    var html_style = "";
                    if(_isObject(fields[j])){
                        if(fields[j]['width']){
                            html_style += "width:" + fields[j]['width'];
                        }

                        // ...
                    }
                    output_tds += ' style="' + html_style + '" ';

                    output_tds += '>' + text + '</td>';
                }


                var tr_class = 'inputpicker-element inputpicker-element-' + i ;


                if ( tr_highlight && highlightResult  ){
                    tr_class += " inputpicker-highlight-active";
                }

                if( value == data[i][ fieldValue ]){
                    tr_class += " inputpicker-active";
                }


                output += '<tr class=" ' + tr_class + '" data-i="' + i + '" data-value="' + data[i][fieldValue] + '">';
                output += output_tds;
                output += '</tr>';
            }

            output += "</tbody>";
        }
        else{
            output += "<thead><tr><td colspan='" + fields.length + "' align='center'>No results.</td></thead></tr>";
        }

        output += _renderPaginationFooter(input);

        output += "</table>";

        wrapped_list.append($(output));

        // Set events
        wrapped_list.find('tbody').find('tr').each(function () {
            var that = $(this);
            that.on('mouseover', function (e) {
                wrapped_list.find('.inputpicker-element').each(function () {
                    $(this).removeClass('inputpicker-active');
                });
                that.addClass('inputpicker-active');
            }).on('click', function (e) {
                var uuid = $('#inputpicker-wrapped-list').data('inputpicker-uuid') ;
                var input = $('#inputpicker-' + uuid);
                var data = _set(input, 'data');

                _setValue(input, $(this).data('value'));
                _o(input).trigger('change');

                // To fix the bug of unable to close automatically in IE.
                if (!_isMSIE()){
                    input.focus();
                }
                _hideWrappedList();

            })
        });


        // Check and change the cursor position if necessary
        var tr_active = _getWrappedListElements(true);
        if ( tr_active.length && ( ( tr_active.position().top + 2 * tr_active.outerHeight()) > wrapped_list.outerHeight()) ) {
            wrapped_list.scrollTop(wrapped_list.scrollTop() + tr_active.data('i') * tr_active.outerHeight());
        }
    }

    /**
     * Change active if find the most match
     * @param input
     * @private
     */
    function _matchActiveInRender(input) {
        var wrapped_list = _getWrappedList(input);
        var fields = _set(input, 'fields');
        var fieldValue = _set(input, 'fieldValue');
        var filterType =  _set(input, 'filterType');
        var filterField =  _set(input, 'filterField');
        var data = _formatData(fieldValue, methods.data.call(input));
        var input_value = input.val();
        var input_value_low = input_value.toString().toLowerCase();


        var tr_active = _getWrappedListElements(true);

        if(!input_value_low){
            return ;
        }

        // Check if is equal
        if (_uuid(wrapped_list) != _uuid(input)){
            return ;
        }

        var shouldBeActive = false;

        if ( tr_active.length){
            var i = tr_active.data('i');
            shouldBeActive = false;

            for (var j = 0; j < fields.length; j++) {
                var field_name = (typeof fields[j] == 'object') ? fields[j]['name'] : fields[j];
                if (typeof data[i][field_name] == 'undefined'){
                    continue;
                }
                var fieldValue = data[i][field_name].toString().toLowerCase();
                if (fieldValue.substr(0, input_value_low.length) == input_value_low) {
                    shouldBeActive = true;
                    break;
                }
            }

            if (shouldBeActive){    // Still active
                return ;
            }
        }

        // Select if can search
        var tr_new_active = null;
        var elements = _getWrappedListElements();
        for(var x = 0; x < elements.length; x++){
            var i = $(elements[x]).data('i');


            for (var j = 0; j < fields.length; j++) {

                var field_name = (typeof fields[j] == 'object') ? fields[j]['name'] : fields[j];
                if (typeof data[i][field_name] == 'undefined'){
                    continue;
                }
                var fieldValue = data[i][field_name].toString().toLowerCase();
                if (fieldValue.substr(0, input_value_low.length) == input_value_low) {
                    tr_new_active = elements[x];
                    break;
                }
            }

            if (tr_new_active){    // Should set active
                if(tr_active.length){
                    tr_active.removeClass('inputpicker-active');
                }
                $(tr_new_active).addClass('inputpicker-active');
                tr_active = $(tr_new_active);

                // dd(tr_active, tr_active.position().top, tr_active.outerHeight(), wrapped_list.scrollTop(), wrapped_list.scrollTop() - tr_active.outerHeight() );
                if (tr_active.position().top < tr_active.outerHeight()) {
                    // wrapped_list.scrollTop(wrapped_list.scrollTop() - tr_active.outerHeight());
                    wrapped_list.scrollTop(tr_active.position().top );
                }
                else if ( ( tr_active.position().top + 2 * tr_active.outerHeight()) > wrapped_list.outerHeight())  {
                    wrapped_list.scrollTop(wrapped_list.scrollTop() + tr_active.data('i') * tr_active.outerHeight());
                }

                return ;
            }
        }

    }


    function _matchHighlightInRender(input) {


        var highlightResult =  _set(input, 'highlightResult');
        if(!highlightResult) return;

        var wrapped_list = _getWrappedList(input);
        var fields = _set(input, 'fields');
        var fieldValue = _set(input, 'fieldValue');
        var filterType =  _set(input, 'filterType');
        var filterField =  _set(input, 'filterField');
        var data = _formatData(fieldValue, methods.data.call(input));
        var input_value = input.val();
        var input_keyword_low = input_value.toString().toLowerCase();


        var tr_active = _getWrappedListElements(true);

        var elements = _getWrappedListElements();
        elements.removeClass('inputpicker-highlight-active');
        if ( !input_keyword_low) return;
        for(var x = 0; x < elements.length; x++){
            var i = $(elements[x]).data('i');


            var inputpick_highlight_active = false;

            for (var j = 0; j < fields.length; j++) {

                var field_name = (typeof fields[j] == 'object') ? fields[j]['name'] : fields[j];
                if (typeof data[i][field_name] == 'undefined'){
                    continue;
                }
                var fieldValue = data[i][field_name].toString().toLowerCase();
                if (fieldValue && fieldValue.indexOf(input_keyword_low) != -1){
                    inputpick_highlight_active = true;
                }
            }

            if (inputpick_highlight_active){    // Should set active
                $(elements[x]).addClass('inputpicker-highlight-active');
            }
        }
    }

    // Check if the specific
    function _isInputVisible(os) {
        var o = os[0];
        return o.offsetWidth > 0 && o.offsetHeight > 0;
    }

    function _isInputWriteable(input) {
        return !input.prop('readonly');
    }

    /**
     * Load data if necessary
     * If url, load from url
     * Else if data is not null, load data into settings
     * Else read data from setting
     * @param input
     * @private
     */
    function _loadData(input, data, func) {
        var original = _o(input);

        if( typeof func == 'undefined' && typeof data == 'undefined'){
            return false;   // Nothing to do
        }

        if( typeof func == 'undefined' && typeof data == 'function'){
            func = data;
            data = null;
        }

        var input_initial_disabled = input.prop('disabled');
        // Add a loading div for
        input.addClass('loading').prop('disabled', true);
        if(_isMSIE())   input.addClass('loading-msie-patch');

        if (_set(input, 'url')){
            // input.prop('disabled', true);
            var param = {};
            if ( _isObject(data) && data){
                param = data;
            }

            _execJSON(input, param, function (ret) {
                var data;

                if(_pagination(input)){
                    _set(input, 'pageCount', ret[ _set(input, 'pageCountField')]);
                }

                if(_isArray(ret)){
                    data = ret;
                }
                else{
                    data = ret['data'];
                }

                // var data = ret['data'];
                // Check and format data
                if (! _isArray(data)  ){
                    _alert( "The type of data(" + ( typeof data ) + ") is incorrect.", input);
                    data = _set(input, 'data');    // Still use old data
                }
                else{   // apply new data
                    // _set(input, 'data', _formatData(_set(input, 'fieldValue'), data) );
                    methods.data.call(input, data);
                }

                // input.removeClass('loading').prop('disabled', false);
                input.removeClass('loading').prop('disabled', input_initial_disabled);
                if(_isMSIE())   input.removeClass('loading-msie-patch');

                if(typeof func == 'function'){
                    // func(input);
                    func.call(this, input, data);
                }
            });
        }
        else{
            if( _isArray(data)){
                // _set(input, 'data', _formatData(_set(input, 'fieldValue'), data) );
                methods.data.call(input, data);
            }
            else{
                data = _set(input, 'data');
            }

            if(typeof func == 'function'){
                // func(input);
                func.call(this, input, data);
            }

            // input.removeClass('loading').prop('disabled', false);
            input.removeClass('loading').prop('disabled', input_initial_disabled);
            if(_isMSIE())   input.removeClass('loading-msie-patch');
        }

    }



    /**
     * Set value
     *     *
     * @param input
     * @param value
     * @private
     * @return true - value changed, false - not changed
     */
    function _setValue(input, value) {

        // if ( typeof )
        if( _typeIsMultiple(input)){
            return _setValueForMultiple(input, value);
        }
        else if( _typeIsTag(input)){
            return _setValueForTag(input, value);
        }
        else{
            var original = _o(input);
            var old_original_value = original.val();

            var fieldValue = _set(input, 'fieldValue');
            var fieldText = _set(input, 'fieldText');
            var data = _set(input, 'data');
            if ( !data.length)  return false;   // No data
            var index_i = -1;
            for(var i = 0; i < data.length; i++){
                if ( data[i][ fieldValue ] == value){
                    index_i = i;
                }
            }

            if ( index_i == -1){    // Did not find

                if(_selectModeIsCreatable(input)){
                    input.val(value);
                    original.val( value);
                }
                else if (_selectModeIsEmpty(input)){
                    dd("res: index_i == -1 and empty, fieldValue:" + fieldValue + "; value: " + value);
                    dd(data);
                    input.val('');
                    input.data('value', '');
                    original.val('');
                    value = '';
                }
                else{   // active and default
                    if ( value ){   // Has value, reset
                        index_i = 0;
                        value = data[index_i][ fieldValue ];
                    }
                    else{   // Not value keep null
                        input.val('');
                        input.data('value', '');
                        original.val('');
                    }
                }
            }

            if (index_i > -1){
                input.val( data[index_i][ fieldText ]);
                input.data('value', data[index_i][fieldValue]); // Check if it is equal the original when selectMode is empty
                original.val( data[index_i][ fieldValue ]);
            }

            return old_original_value != value; // If changed
        }

    }

    function _setValueForTag(input, value) {
        return _setValueForMultiple(input, value);
    }

    function _setValueForMultiple(input, value) {
        var original = _o(input);
        var old_original_value = original.val();
        var fieldValue = _set(input, 'fieldValue');
        var fieldText = _set(input, 'fieldText');
        var delimiter = _set(input, 'delimiter');
        var data = _set(input, 'data');
        if ( !data.length)  return false;   // No data

        var arr_values = value ? value.toString().split(delimiter) : [];
        var new_values = [];
            // dd('old_original_value: ' + old_original_value + ' ; _setValueForMultiple: ' + value + ' ; arr= ', arr);
        var new_data = [];


        for(var i = 0; i < data.length; i++){
            if ( -1 < _inArray(data[i][ fieldValue ], arr_values ) ){
                new_values.push(data[i][ fieldValue ]);
                new_data.push(data[i]);
            }
        }

        var o_v = old_original_value,
            n_v = new_values.sort().join(delimiter);

        new_values = new_values.join(delimiter);

        original.val(new_values);

        // Render
        var ul_multiple = _getInputpickerDiv(input).find('.inputpicker-multiple').find('ul');
        // dd(ul_multiple.length);
        ul_multiple.find("li.inputpicker-element").remove();
        var li_input = input.closest('li');

        for(var i = 0; i < new_data.length; i++){
            var d = new_data[i];
            $("<li class=\"inputpicker-element\" data-value=\"" + d[fieldValue] + "\"><span>" + d[fieldText] + "</span> <a href=\"javascript:void(0);\" onclick=\"$(this).closest('.inputpicker-div').find('input').inputpicker('removeValue', $(this).parent().data('value') );event.stopPropagation();\" onmouseover=\"$(this).prev().addClass();\" tabindex='-1'>x</a></li>").insertBefore(li_input);
        }

        // input.remove();

        return o_v != n_v;
    }

    /**
     * Set selected as value
     * @param uuid
     * @private
     */
    function _setValueByActive(input) {

        if (_typeIsMultiple(input)){
            return _setValueByActiveForMultiple(input);
        }
        else if (_typeIsTag(input)){
            return _setValueByActiveForMultiple(input);
        }
        else{

            var wrapped_list = _getWrappedList();
            var tr_active = _getWrappedListElements(true);

            // Check if is equal
            if (_uuid(wrapped_list) != _uuid(input)){
                return false;
            }

            if ( tr_active.length){
                // dd('tr_active.length:' , tr_active.length);
                return _setValue(input, tr_active.data('value'));
            }
            else{   // Not selected, No any change
                return false;
            }
        }




    }
    
    function _setValueByActiveForMultiple(input) {


        var wrapped_list = _getWrappedList();
        var tr_active = _getWrappedListElements(true);

        // Check if is equal
        if (_uuid(wrapped_list) != _uuid(input)){
            return false;
        }

        if ( tr_active.length){
            // dd('tr_active.length:' , tr_active.length);
            // return _setValue(input, tr_active.data('value'));

            var self = tr_active;
            var delimiter = _set(input, 'delimiter');
            var value = _o(input).val();
            var arr_value = value ? value.toString().split(delimiter) : [];

            var selected = self.hasClass('inputpicker-selected');
            var i = _inArray( self.data('value'), arr_value);
            if (selected && i > -1)   // Need to remove it
            {
                arr_value.splice(i, 1);
                self.removeClass('inputpicker-selected');

            }
            else if( !selected ){   // Not selected, need to select
                arr_value.push(self.data('value'));
                self.addClass('inputpicker-selected');
            }

            tr_active.removeClass('inputpicker-active');

            return _setValue(input, arr_value.join(delimiter));
        }
        else{   // Not selected, No any change
            return false;
        }
        
    }
    
    
    
    
    

    function _isWrappedListVisible(input) {
        var wrapped_list = _getWrappedList();
        if (wrapped_list.is(':visible') && _uuid(wrapped_list) == _uuid(input)){
            return true ;
        }
        else{
            return false;
        }
    }

    function _hideWrappedList(ifReset) {
        var wrapped_list = _getWrappedList();
        if(typeof ifReset != 'undefined' && ifReset) {
            _uuid(wrapped_list, 0);
        }
        return wrapped_list ? wrapped_list.hide() : false;
    }

    /**
     * Get elements
     * @param isActive
     *      undefined   all elements
     *      true        active
     *      false       unactive
     * @private
     */
    function _getWrappedListElements(isActive) {

        var p = '.inputpicker-element';
        if (typeof isActive != 'undefined' ){
            p += isActive ? '.inputpicker-active' : ':not(.inputpicker-active)';
        }
        return _getWrappedList().find(p);
    }

    // function _getWrappedListElement(i) {
    //     return _getWrappedList().find('inputpicker-element-' + i);
    // }
    //
    // function _getWrappedListSelectedElement() {
    //     return _getWrappedList().find('tr.selected');
    // }


    function _generateUid() {
        if(!window.inputpickerUUID) window.inputpickerUUID = 0;
        return ++window.inputpickerUUID;
    }

    function _isMSIE()
    {
        var iev=0;
        var ieold = (/MSIE (\d+\.\d+);/.test(navigator.userAgent));
        var trident = !!navigator.userAgent.match(/Trident\/7.0/);
        var rv=navigator.userAgent.indexOf("rv:11.0");

        if (ieold) iev=new Number(RegExp.$1);
        if (navigator.appVersion.indexOf("MSIE 10") != -1) iev=10;
        if (trident&&rv!=-1) iev=11;

        return iev;
    }

    function _setValueForInput(input) {

        if ( _selectModeIsActive(input) ){   // Change value by tab
            if (_setValueByActive(input)) {  // Value changed
                _o(input).trigger('change');
            }
        }
        else if (_selectModeIsCreatable(input)){      // Set the current keyword is new value
            if(!_inputValueEqualToOriginalValue(input)){
                _o(input).val(_i(input).val()).trigger('change');
            }
        }
        else if (_selectModeIsEmpty(input)){ // Set the value is empty if does not find
            if ( _i(input).val() != '' && (!_i(input).data('value')) && (!_o(input).val())){
                _i(input).val('');
            }
            else if(!_inputValueEqualToOriginalValue(input)){
                _i(input).val('');
                _i(input).data('value', '');
                _o(input).val('').trigger('change');
            }
        }
        else{   // restore
            if( _i(input).val()){   // If input is not ''
                _setValue(input, _o(input).val());
            }
            else{
                // trigger change if activate
                var old_value = _o(input).val();
                _setValue(input, '');
                if ( old_value != ''){
                    _o(input).trigger('change');
                }
            }
        }
    }

    /**
     * The input is focused
     * @param e
     * @private
     */
    function _eventFocus(e) {
        var input = _i($(this));
        if(_set(input, "autoOpen")){
            methods.show.call(input, e);
        }
    }

    function _eventBlur(e) {
        var input = _i($(this));
        var original = _o(input);


        if ( _selectModeIsEmpty(input)){
            return;
        }
        //_setValueForInput(input); ???




        // _hideWrappedList();



        // Clear invalid value
        // if (input.data('inputpicker-i') == -1){
        //     input.val('');
        //     original.val('');
        // }
    }

    function _eventKeyDown(e) {
        var input = $(this);
        var wrapped_list = _getWrappedList();
        // // Close if the wrapped list is invisible
        // if(!_isWrappedListVisible()){
        //     e.stopPropagation();
        //     e.preventDefault();
        //     return;
        // }
        dd( _name(input) + '._eventKeyDown:' + e.keyCode + '; charCode:' + e.charCode);


        switch(e.keyCode){
            case 37:    // Left
            case 38:    // Up
                //_changeWrappedListSelected
                methods.show.call(input, e);
                // var tr_selected = wrapped_list.find('tr.selected');
                // if ( tr_selected.prev('.inputpicker-element').length ){
                //     tr_selected.removeClass('selected').prev('.inputpicker-element').addClass('selected');
                //     if (tr_selected.prev().position().top < tr_selected.outerHeight()) {
                //         wrapped_list.scrollTop(wrapped_list.scrollTop() - tr_selected.outerHeight());
                //     }
                // }
                _changeWrappedListSelected(input, -1);
                break;
            case 39:    // Down
            case 40:    // Down
                methods.show.call(input, e);
                // var tr_selected = wrapped_list.find('tr.selected');
                // if (tr_selected.next('.inputpicker-element').length) {
                //     tr_selected.removeClass('selected').next('.inputpicker-element').addClass('selected');
                //     if ( ( tr_selected.next().position().top + 2 * tr_selected.outerHeight()) > wrapped_list.outerHeight()) {
                //         wrapped_list.scrollTop(wrapped_list.scrollTop() + tr_selected.outerHeight());
                //     }
                // }
                _changeWrappedListSelected(input, 1);
                break;
            case 27:    // Esc
                if( _selectModeIsCreatable(input)) {
                    if(!_inputValueEqualToOriginalValue(input)){
                        _o(input).trigger('change');
                    }
                }
                else{
                    _setValue(input, _o(input).val());
                }
                _hideWrappedList();
                break;
            case 9: // Tab
                _setValueForInput(input);
                _hideWrappedList();
                break;
            case 13:    // Enter
                e.preventDefault(); // Prevent from submitting form
                methods.toggle.call(input, e);
                if ( _setValueByActive(input) ){
                    _o(input).trigger('change');
                }
                else{
                    if( _isCreatable(input)){
                        if(!_inputValueEqualToOriginalValue(input)){
                            _o(input).trigger('change');
                        }
                    }
                }
                break;
            case 8:    // Backspace

                if (input.val() == '' && ( _typeIsMultiple(input) || _typeIsTag(input) ) && _getInputMultipleElements(input).length){
                    var original = _o(input);
                    var o_value = original.val().split(_set(input, 'delimiter'));
                    if(o_value.length){
                        o_value.pop();
                        original.val(o_value).trigger('change');
                    }
                    // _getInputMultipleElements(input).last().remove();
                }
                break;
            default:
                // Characters
                input.data('value', '');

                break;
        }
    }

    function _eventKeyUp(e) {
        var input = $(this);
        var wrapped_list = _getWrappedList();
        // if(!_isWrappedListVisible()){
        //     e.stopPropagation();
        //     e.preventDefault();
        //     return;
        // }

        //, e.which, String.fromCharCode(e.which), e.which !== 0 && e.charCode !== 0
        dd( _name(input) + '._eventKeyUp:' + e.keyCode );

        if ($.inArray( e.keyCode, [37, 38, 39, 40, 27, 9, 13, 16, 17, 18]) != -1 ){
            return ;
        }


        // Keyword changes
        if ( _set(input, 'url')){

            // If it is backspace, and data.length is not empty do not change
            var data = _set(input, 'data');
            if (e.keyCode == 8 && data.length ){

                _matchHighlightInRender(input);
                return ;
            }




            // Check if delay
            var delay = parseFloat(_set(input, 'urlDelay'));
            var delayHandler = _set(input, 'delayHandler');
            if ( delayHandler ){
                clearTimeout(delayHandler);
                _set(input, 'delayHandler', false);
            }


            delayHandler = setTimeout(_loadData, delay * 1000, input, function (input) {
                _dataRender(input);
                var wrapped_elements = _getWrappedListElements();
                if ( _isWrappedListVisible(input) && _getWrappedListElements(true).length == 0 &&  wrapped_elements.length){
                    wrapped_list.first().addClass('inputpicker-active');
                }
                _matchActiveInRender(input);
                _matchHighlightInRender(input);
                if(!input.is(":focus")) {
                    dd('focus', input)
                    input.focus();
                }

            } );
            _set(input, 'delayHandler', delayHandler);


            // _loadData(input, function (input) {
            //     _render(input);
            //
            //     var wrapped_elements = _getWrappedListElements();
            //     if ( _isWrappedListVisible(input) && _getWrappedListElements(true).length == 0 &&  wrapped_elements.length){
            //         wrapped_list.first().addClass('active');
            //     }
            //
            // });
        }
        else{
            if(_set(input, 'filterOpen')){  // Need to render with filtering
                _dataRender(input);
            }
            else{   // show straightway
                methods.show.call(input, e);
            }

            // Active matched ?

            var wrapped_elements = _getWrappedListElements();
            if ( _isWrappedListVisible(input) && _getWrappedListElements(true).length == 0 &&  wrapped_elements.length){
                wrapped_list.first().addClass('inputpicker-active');
            }
            _matchActiveInRender(input);
            _matchHighlightInRender(input);

        }
    }

    function _isDefined(v) {
        return typeof v != 'undefined';
    }

    function _isObject(v) {
        return typeof v == 'object';
    }

    function _isArray(v) {
        return Array.isArray(v);
    }

    function _inArray(k, a) {
        for(var i = 0; i < a.length; i++){
            if(k == a[i]){
                return i;
            }
        }
        return -1;
    }

    // -------------------------------------------------------------------------------------------
    jQuery.propHooks.disabled = {
        set: function (el, value) {
            if (el.disabled !== value) {
                el.disabled = value;
                value && $(el).trigger('disable.inputpicker');
                !value && $(el).trigger('enable.inputpicker');
            }
        }
    };

    $.fn.inputpicker = function (method) {
        if(!this.length) return this;
        if(typeof method == 'object' || !method){
            return methods.init.apply(this, arguments);
        }
        else if(methods[method]){
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        }
        else { $.error("Method "+ method + " does not exist on jQuery.inputpicker"); }
    }


    $.fn.inputpicker.defaults = {

        /**
         * Width , default is 100%
         */
        width: '100%',

        /**
         * Default Height
         */
        height: '200px',

        /**
         * Selected automatically when focus
         */
        autoOpen: false,

        /**
         * Press tab to select automatically
         */
        tabToSelect: false,


        creatable : false,    // Allow user creates new value when true,

        /**
         * The action after pressing 'tab'
         * restore: Use the previous value, the change event is not raised.
         * active: Use the active option
         * new: Use the current keyword,
         * null : Set the word is null
         */
        selectMode : 'restore',


        /**
         * True - show head
         * False
         */
        headShow : false,   // true : show head, false: hide


        /**
         * Support multiple values
         */
        multiple : false,

        /**
         * Tag
         */
        tag : false,

        /**
         * Delimiter for multiple values
         */
        delimiter: ',',

        /**
         * Data
         */
        data: [],

        /**
         * Fields
         * Store fields need to been shown in the list
         * (Sting) - 'value'
         * (Object) - {name:'value', text:'Value'}
         */
        fields: [],

        /**
         * The field posting to the field
         */
        fieldValue: 'value',

        /**
         * The field shown in the input
         * Will use fieldValue if empty
         */
        fieldText :'',


        // filter Setting

        /**
         * True - filter rows when changing the input content
         * False - do not do any spliation
         */
        filterOpen: false,

        /**
         * Choose the method of filtering
         * 'start' - start filtering from the beginning
         * others - all content matches
         */
        filterType: '',  // 'start' - start from beginning or ''

        /**
         * Choose the fields need to be filtered
         * (String)'name' - one field
         * (Array)['name', 'value'] - multiple fields
         */
        filterField: '',

        /**
         * Limit number
         */
        limit: 0,

        // --- URL settings --------------------------------------------
        url: '',    // set url

        urlCache: false,

        /**
         * Set url params for the remote data
         */
        urlParam: {},

        /**
         * Headers for json request
         */
        urlHeaders: {},

        /**
         * If search interval is too short, will execute
         */
        urlDelay: 0,

        /**
         * pagination
         */
        pagination: false,   // false: no

        pageMode: '',  // The Pagination mode: '' is the default style; 'scroll' is the scroll dragging style

        pageField: 'p', // Page File Name for request

        pageLimitField: 'limit', // Page Limit Field name for request

        // pageLimit: 10,  // Page limit for request -- deprecated due to replication with the 'limit' field

        pageCurrent: 1, // Current page

        pageCountField: 'count',
        pageCount:0,    // System uses


        listBackgroundColor: '',
        listBorderColor: '',
        rowSelectedBackgroundColor: '',
        rowSelectedFontColor : '',

        // Un-necessary - Use Pagination
        // pagination: false,


        // All the result match keywords will highlight, only
        highlightResult : false,


        responsive: true,

        _bottom: ''

    };

    // $.fn.inputpicker.wrapped_list = null;

    $.fn.inputpicker.elements = [{}];
});