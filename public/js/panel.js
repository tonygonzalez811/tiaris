/**
 * Created by Alfredo on 14/09/14.
 */

var Panel = {
    loadingIcon: 'fa-circle-o-notch fa-spin',
    icons: 'fa-search fa-plus fa-check fa-exclamation-triangle fa-info-circle',
    allIcons: function() {
        return Panel.loadingIcon + ' ' + Panel.icons;
    },

    //slides a panel down
    expand: function($panel) {
        $panel.find('.expand').eq(0).click();
        setTimeout(function() {
            $panel.find('input').eq(0).focus();
        }, 500);
    },

    //slides a panel up
    collapse: function($panel) {
        $panel.find('.collapse').eq(0).click();
    },
    
    //unhides a panel
    show: function($panel) {
        var exp = $panel.find('.expand').eq(0);
        $panel.removeClass('hidden');
        if (exp.length) { //if collapsed
            exp.click();
        }
        else {
            $panel.addClass('animated pulse');
        }
        setTimeout(function() {
            $panel.find('input').not('input[type=hidden]').eq(0).focus();
        },500);
        setTimeout(function() {
            $panel.removeClass('animated pulse');
        },2000);
    },

    //cleans all inputs from a form
    resetForm: function($frm) {
        $frm.find('input[type=text]').val("");
        $frm.find('input[type=email]').val("");
        $frm.find('input[type=password]').val("");
        $frm.find('input[type=tel]').val("");
        $frm.find('input[type=number]').val("0");
        $frm.find('input[type=url]').val("");
        $frm.find('input[type=hidden]').not('input[name=_token]').not('.static-value').val("");
        $frm.find('textarea').val("");
        $frm.find('input[type=checkbox]').prop('checked', false); //TODO: consider adding a 'reset-to' attribute to the html input and use it here
        $frm.find('select option').removeAttr('selected');
        if (typeof Select2 != 'undefined') {
            var $items = $frm.find('select');//.select2('val', '');
            $.each($items, function(i, o) {
                var $o = $(o);
                if ($o.prop('multiple')) {
                    $o.select2('val', null);
                }
                else {
                    $o.select2('val', $o.find('option:first').val());
                }
            });
            $frm.find('input[type=hidden].select2ajax').select2('val','');
            $frm.find('input[type=hidden].select2tags').select2('val','');
        }
        if (typeof $.fn.slider != 'undefined') {
            $frm.find('.input-slider[data]').slider('setValue', 0);
        }
    },

    //takes the json data and use it to fill a form
    objectToInputs: function($frm, data) {
        console.log($frm.attr('name'));
        console.log(data);
        var o;
        $.each(data, function(key, value) {
            if (key != 'ok') {
                o = $frm.find('[name=' + key + ']');
                if (!o.length) { o = $frm.find('#' + key + '_edit'); }
                if (!o.length) { o = $frm.find('#' + key); }
                if (o.length) {
                    switch (o.prop('tagName')) {
                        case 'INPUT':
                            if (o.attr('type') == 'text') {
                                //date
                                if (o.hasClass('input-calendar-year') || o.hasClass('input-calendar-day')) {
                                    //o.datepicker('update', value);
                                    setDatePicker(o, value);
                                }
                                //time
                                else if (o.hasClass('input-time')) {
                                    if (typeof value == 'string') {
                                        setTimePicker(o, value);
                                    }
                                    else {
                                        o.pickatime('picker').clear();
                                    }
                                }
                                //slider
                                else if (o.hasClass('input-slider') && (typeof o.attr('data') != 'undefined')) {
                                    o.slider('setValue', parseInt(value));
                                }
                                //dni
                                else if (o.hasClass('input-dni')) {
                                    var dni = value.split('-');
                                    o.parent().find('button').find('span').html( dni[0] + '-' );
                                    o.val( dni[1] );
                                }
                                else {
                                    o.val( value );
                                }
                            }
                            else if (o.attr('type') == 'checkbox') {
                                if (o.hasClass('switch') && typeof $.fn.bootstrapSwitch == 'function') {
                                    o.bootstrapSwitch('state', value == 1, true);
                                }
                                o.prop('checked', value == 1);
                            }
                            else if (o.attr('type') == 'hidden') {
                                if (o.hasClass('select2ajax')) {
                                    if (typeof Select2 != 'undefined') {
                                        //for an ajax select2 there should be a key for the label. Ex.: 'usuario_id' should have 'usuario_id_lbl' as its label
                                        if (typeof data[key + '_lbl'] == 'undefined' || data[key + '_lbl'] == '') {
                                            o.select2('val', '');
                                        }
                                        else {
                                            o.select2('data', {id:value, text:data[key + '_lbl']});
                                        }
                                    }
                                }
                                else if (o.hasClass('select2tags')) {
                                    if (typeof Select2 != 'undefined') {
                                        console.log('tags select2: ' + value);
                                        o.select2('val', value.split(','));
                                    }
                                }
                                else {
                                    o.val( value );
                                }
                            }
                            else {
                                o.val( value );
                            }
                            break;

                        case 'SELECT':
                            var sels;
                            if ( (typeof value == 'object' &&  value != null) || typeof value == 'array') {
                                sels = [];
                                $.each(value, function(k, v) {
                                    //o.find('option[value=' + k + ']').attr('selected', 'selected');
                                    //o.select2('val', k);
                                    sels.push( k );
                                });
                            }
                            else {
                                sels = value;
                            }
                            /*o.select2("destroy");
                            o.select2();*/
                            if (typeof Select2 != 'undefined' && sels != null) {
                                console.log('selecting ' + sels);
                                o.select2('val', sels);
                            }
                            break;
                        //o.find('option[value=' + key + ']').attr('selected', 'selected');

                        default:
                            o.val( value );
                    }
                }
            }
        });
    },

    //takes the json data and use it to change element's html content
    objectToLabels: function($frm, data) {
        var o;
        $.each(data, function(key, value) {
            o = $frm.find('#view_' + key);
            if (o.length) {
                o.html( value );
            }
        });
    },


    /**
     * Counter
     */
    counter: {

        update: function() {
            if (typeof url_update_counter == 'string') {
                $.ajax({
                    type: 'GET',
                    url: url_update_counter,
                    dataType: 'json'
                }).done(function(data) {
                    //console.log(data);
                    if (data['ok']) {
                        Panel.counter.setTo( data['total'] );
                        if (typeof afterUpdatingRecords == 'function') {
                            afterUpdatingRecords();
                        }
                    }
                }).fail(function(data) {
                    console.log(data); //failed
                });
            }
        },

        setTo: function(val) {
            var $total = $('#total_records');
            $total.html( val );
            $total.addClass('animated pulse');
            setTimeout(function() { $total.removeClass('animated'); }, 1000);
        }

    },


    /**
     * Panel 'Search'
     */
    search: {

        form: function() {
            return $('#frm_data_search');
        },

        //slides the panel down
        expand: function() {
            Panel.expand( $('#search_panel') );
        },

        //slides the panel up
        collapse: function() {
            Panel.collapse( $('#search_panel') );
        },

        //removes html content from result list
        clearResults: function() {
            var $panel = $('#search_panel');
            var $sr = $panel.find('.search-results-holder');
            $sr.slideUp('fast', function() {
                $sr.html("");
            });
        },

        bindResultsClickEvent: function() {
            var $panel = $('#search_panel');

            //load link
            $panel.find('a.search-result').click(function() {
                Panel.view.show();
                Panel.view.load( $(this).attr('data-id') );
                Panel.search.collapse();
            });

            //pagination links
            $panel.find('ul.search-results-pagination').find('a').click(function(e) {
                if ($(this).parent().is('.disabled,.active')) return false;
                var query = Panel.search.form().find('input.search-query').val();
                var page;
                var link = $(this);
                //page number
                if (link.hasClass('search-results-page')) {
                    page = $(this).attr('page-num');
                }
                //previous page
                else if (link.hasClass('search-results-prev-page')) {
                    page = (parseInt(Panel.search.form().find('input.search-page').val()) || 2) - 1;
                }
                //next page
                else {
                    page = (parseInt(Panel.search.form().find('input.search-page').val()) || 1) + 1;
                }

                Panel.search.find( query, page );

                e.preventDefault();
                return false;
            });
        },

        update: function() {
            var query = Panel.search.form().find('input.search-query').val();
            if (query.length > 0) {
                Panel.search.find( Panel.search.form().find('input.search-query').val() );
            }
        },

        //sends a look up string to the server and retrieves data
        find: function(query, page, fn) {
            console.log(page);
            Panel.search.clearResults();
            Panel.search.status.loading();

            var $frm = Panel.search.form();
            var url = $frm.attr('action');
            if (typeof query == "undefined") query = $frm.find('input[name=search]').val();
            if (!(parseInt(page) > 1)) page = 1;

            $frm.find('input.search-query').val( query );
            $frm.find('input.search-page').val( page );

            $.ajax({
                type: 'GET',
                url: url,
                dataType: 'json',
                data: $frm.serialize()//{ 'query': query, 'page': page }
            }).done(function(data) {
                console.log(data);
                if (data['ok']) {
                    var $frm = Panel.search.form().parent();
                    var $sr = $frm.find('.search-results-holder').eq(0);

                    if (data['total'] > 0) {
                        //var items = "";
                        /*$.each(data['results'], function(i,v) {
                            items += '<a class="list-group-item">' + v + '</a>';
                        });*/
                        setTimeout(function() {
                            $sr.html((query != '*' ? '<p>Resultados de la búsqueda:</p>' : '') + '<div class="list-group search-results">' + data['results'] + '</div>');

                            //pagination:
                            var total_per_page = 5;
                            var pag = '';
                            if (page > 1 || data['total'] > data['total_page']) {
                                pag = '<ul class="pagination search-results-pagination">' +
                                    '<li' + (page == 1 ? ' class="disabled"':'') + '><a class="search-results-prev-page" href="#">&laquo;</a></li>' +
                                    '<li' + (page == 1 ? ' class="active"':'') + '><a class="search-results-page" page-num="1" href="#">1</a></li>';
                                var p = 2;
                                for (var i = total_per_page;  i < data['total'];  i += total_per_page) {
                                    pag += '<li' + (page == p ? ' class="active"':'') + '><a class="search-results-page" page-num="' + p + '" href="#">' + p + '</a></li>';
                                    p++;
                                }
                                pag += '<li' + (page >= p-1 ? ' class="disabled"':'') + '><a class="search-results-next-page" href="#">&raquo;</a></li>' +
                                    '</ul>';
                            }

                            $sr.html($sr.html() + pag);

                            if (typeof data['script'] != 'undefined') {
                                console.log('evaling: ' + data['script']);
                                eval( data['script'] );
                            }

                            Panel.search.bindResultsClickEvent();
                            $sr.slideDown();
                        }, 200);
                    }
                    else {
                        setTimeout(function() {
                            $sr.html('<p>No se encontrarón registros.</p>');
                            $sr.slideDown();
                        }, 200);
                    }

                    if (typeof fn == 'function') {
                        fn(data);
                    }
                    if (typeof afterSearchingRecords == 'function') {
                        afterSearchingRecords(data);
                    }
                    Panel.search.status.found(data['total']);
                }
                else {
                    Panel.search.status.error(data['err']);
                }
            }).fail(function(data) {
                console.log(data); //failed
                Panel.search.status.error(data['responseText'].substr(0, 200));
            });
        },

        //sets a style for the Search panel
        status: {
            loading: function() {
                $('#search_icon').removeClass(Panel.icons).addClass(Panel.loadingIcon);
                $('#search_lbl').html('Buscando...');
                App.blockUI( $('#search_panel') );
            },
            found: function(n) {
                $('#search_icon').removeClass(Panel.allIcons()).addClass('fa-search');
                $('#search_lbl').html('Encontrado' + (n!=1?'s ':' ') + n + ' registro' + (n!=1?'s':''));
                App.unblockUI( $('#search_panel') );
            },
            error: function(err) {
                $el = $('#search_icon').removeClass(Panel.allIcons()).addClass('fa-exclamation-triangle');
                $('#search_lbl').html(err);
                $el = $el.parent().addClass('animated flash');
                setTimeout(function() {
                    $el.removeClass('animated');
                },4000);
                Panel.search.expand();
                App.unblockUI( $('#search_panel') );
            },
            restore: function() {
                $('#search_icon').removeClass(Panel.allIcons()).addClass('fa-search').parent().removeClass('flash');
                $('#search_lbl').html('Buscar');
                App.unblockUI( $('#search_panel') );
            }
        }
    },

    /**
     * Panel 'View'
     */
    view: {

        form: function() {
            return $('#frm_data_view');
        },

        //slides the panel down
        expand: function() {
            Panel.expand( $('#view_panel') );
        },

        //slides the panel up
        collapse: function() {
            Panel.collapse( $('#view_panel') );
        },

        show: function() {
            Panel.show( $('#view_panel') );
        },

        //loads data from server
        load: function(id, fill_inputs, fn_callback) {
            Panel.view.status.loading();
            var $frm = $('#frm_info_get'); //Panel.view.form();
            var url = $frm.attr('action');
            $.ajax({
                type: 'GET',
                url: url,
                dataType: 'json',
                data: $frm.serialize() + '&id=' + id
            }).done(function(data) {
                //console.log(data);
                if (data['ok']) {
                    /*if (fill_inputs == "undefined" || fill_inputs) {
                        Panel.objectToLabels($('#frm_data_view'), data);
                    }*/
                    Panel.view.form().find('.content').html( data['results'] );

                    if (typeof data['script'] != 'undefined') {
                        console.log('evaling: ' + data['script']);
                        eval( data['script'] );
                    }

                    if (typeof fn == "function") {
                        fn_callback(data);
                    }
                    //Panel.view.status.restore();
                    Panel.view.status.setTo( data['title'] );
                }
                else {
                    Panel.view.status.error(data['err']);
                }
            }).fail(function(data) {
                console.log(data); //failed
                Panel.edit.status.error(data['responseText'].substr(0, 200));
            });
        },

        reload: function() {
            Panel.view.load( Panel.view.form().find('input[name=id]').val() );
        },

        submit: function() {
            var $frm = Panel.view.form();
            var clicked_btn = $frm.find('button[type=submit]:focus').attr('name'); //:focus selector might not work in all browsers (!)

            if (clicked_btn == 'action_delete') {
                if (!confirm('¿Está seguro que quiere eliminar este registro?')) {
                    return false;
                }
            }
            else if (clicked_btn == 'action_edit') {
                Panel.view.collapse();
                Panel.edit.show();

                Panel.edit.load( $frm.find('input[name=id]').val() );
                return true;
            }

            $frm.find('input[name=action]').val( clicked_btn );

            Panel.view.status.loading();
            var url = $frm.attr('action');
            $.ajax({
                type: 'POST',
                url: url,
                dataType: 'json',
                data: $frm.serialize() // serializes the form's elements.
            }).done(function(data) {
                //console.log(data);
                if (data['ok']) {
                    if (data['deleted'] == 1) {
                        Panel.view.status.cleared('Eliminado.');
                        Panel.counter.update();
                        Panel.search.update();
                    }
                }
                else {
                    Panel.view.status.error(data['err']);
                }
            }).fail(function(data) {
                console.log(data); //failed
                Panel.view.status.error(data['responseText'].substr(0, 200));
            });
        },

        //sets a style for the View panel
        status: {
            loading: function() {
                $('#view_icon').removeClass(Panel.icons).addClass(Panel.loadingIcon);
                $('#view_lbl').html('Cargando...');
                App.blockUI( $('#view_panel') );
            },
            setTo: function(lbl) {
                $('#view_icon').removeClass(Panel.allIcons()).addClass('fa-info-circle');
                $('#view_lbl').html(lbl);
                App.unblockUI( $('#view_panel') );
            },
            error: function(err) {
                $el = $('#view_icon').removeClass(Panel.allIcons()).addClass('fa-exclamation-triangle');
                $('#view_lbl').html(err);
                $el = $el.parent().addClass('animated flash');
                setTimeout(function() {
                    $el.removeClass('animated');
                },4000);
                Panel.view.expand();
                App.unblockUI( $('#view_panel') );
            },
            restore: function(expand) {
                $('#view_icon').removeClass(Panel.allIcons()).addClass('fa-info-circle').parent().removeClass('flash');;
                $('#view_lbl').html('Información');
                if (typeof expand == "undefined" || expand) {
                    Panel.view.expand();
                }
                App.unblockUI( $('#view_panel') );
            },
            cleared: function(lbl) {
                var vpanel = $('#view_panel');
                $('#view_icon').removeClass(Panel.allIcons()).addClass('fa-info-circle');
                $('#view_lbl').html(lbl);
                Panel.view.collapse();
                vpanel.find('.content').html('');
                App.unblockUI( vpanel );
            }
        }
    },

    /**
     * Panel 'Create new'
     */
    create: {

        form: function() {
            return $('#frm_data_new');
        },

        //slides the panel up
        expand: function() {
            Panel.expand( $('#create_panel') );
        },

        //slides the panel down
        collapse: function() {
            Panel.collapse ( $('#create_panel') );
        },

        show: function() {
            if (typeof beforePanelCreate == 'function') {
                beforePanelCreate();
            }
            var $frm = $('#create_panel');
            var $dyinputs = $frm.find('.input-calendar-year');
            var $ddinputs = $frm.find('.input-calendar-day');
            if (typeof $.fn.pickadate != 'undefined') {
                $dyinputs.prop("disabled", true);//attr('disabled', 'disabled');
                $ddinputs.prop("disabled", true);//.attr('disabled', 'disabled');
                /*$.each($dyinputs, function(i,o) {
                    $(o).pickadate('picker').stop();
                });
                $.each($ddinputs, function(i,o) {
                    $(o).pickadate('picker').stop();
                });*/
            }
            Panel.show( $('#create_panel') );
            if (typeof $.fn.pickadate != 'undefined') {
                setTimeout(function() {
                    $dyinputs.prop("disabled", false);
                    $ddinputs.prop("disabled", false);
                    /*$.each($dyinputs, function(i,o) {
                        $(o).pickadate('picker').start();
                    });
                    $.each($ddinputs, function(i,o) {
                        $(o).pickadate('picker').start();
                    });*/
                }, 1000);
            }
        },

        //clears the values from the inputs
        resetForm: function() {
            Panel.resetForm( $('#frm_data_new') );
        },

        //sends form data to server
        save: function() {
            Panel.create.status.saving();
            var $frm = Panel.create.form();
            var url = $frm.attr('action');
            var with_file = $frm.hasClass('with-file');
            var data_values;
            if (with_file) {
                data_values = new FormData($frm[0]);
            }
            else {
                data_values = $frm.serialize();
            }
            $.ajax({
                type: 'POST',
                url: url,
                dataType: 'json',
                data: data_values,
                processData: !with_file,
                contentType: !with_file ? 'application/x-www-form-urlencoded; charset=UTF-8' : false
            }).done(function(data) {
                //console.log(data);
                if (data['ok']) {
                    Panel.create.resetForm();
                    Panel.create.status.saved();
                    Panel.counter.update();
                }
                else {
                    Panel.create.status.error(data['err']);
                }
            }).fail(function(data) {
                console.log(data); //failed
                Panel.create.status.error(data['responseText'].substr(0, 200));
            });
        },

        //sets a style for the Create-new panel
        status: {
            saving: function() {
                $('#create_icon').removeClass(Panel.icons).addClass(Panel.loadingIcon);
                $('#create_lbl').html('Guardando...');
                App.blockUI( $('#create_panel') );
            },
            saved: function() {
                $('#create_icon').removeClass(Panel.allIcons()).addClass('fa-check');
                $('#create_lbl').html('Guardado.');
                Panel.create.collapse();
                //binds an event to restore status if user enters new data
                $('#frm_data_new').find('input').on('change.after_saved', function() {
                    Panel.create.status.restore(false);
                    //unbinds the event; no longer needed
                    $('#frm_data_new').find('input').off('change.after_saved');
                });
                App.unblockUI( $('#create_panel') );
            },
            error: function(err) {
                $el = $('#create_icon').removeClass(Panel.allIcons()).addClass('fa-exclamation-triangle');
                $('#create_lbl').html(err);
                $el = $el.parent().addClass('animated flash');
                setTimeout(function() {
                    $el.removeClass('animated');
                },4000);
                Panel.create.expand();
                App.unblockUI( $('#create_panel') );
            },
            restore: function(expand) {
                if (typeof expand != 'boolean') expand = true;
                $('#create_icon').removeClass(Panel.allIcons()).addClass('fa-plus').parent().removeClass('flash');;
                $('#create_lbl').html('Nuevo');
                if (expand) {
                    Panel.create.expand();
                    App.unblockUI($('#create_panel'));
                }
            }
        }
    },

    /**
     * Panel 'Edit'
     */
    edit: {

        form: function() {
            return $('#frm_data_edit');
        },

        //slides the panel up
        expand: function() {
            var $panel = $('#edit_panel');
            $panel.find('.expand').eq(0).click();
            setTimeout(function() {
                $panel.find('input').eq(0).focus();
            }, 500);
        },

        //slides the panel down
        collapse: function() {
            $('#edit_panel').find('.collapse').eq(0).click();
        },

        //displays the panel in an expanded way
        show: function() {
            if (typeof beforePanelEdit == 'function') {
                beforePanelEdit();
            }
            var panel = $('#edit_panel');
            var exp = panel.find('.expand').eq(0);
            panel.removeClass('hidden');
            if (exp.length) { //if collapsed
                exp.click();
            }
            else {
                panel.addClass('animated pulse');
            }
            setTimeout(function() {
                panel.find('input').eq(0).focus();
            },500);
            setTimeout(function() {
                panel.removeClass('animated pulse');
            },2000);
        },

        //clears the values from the inputs
        resetForm: function() {
            Panel.resetForm( Panel.edit.form() );
        },

        //loads data from server
        load: function(id, fill_inputs, fn_callback) {
            console.log('finding ' + id + '...');
            var $frm = $('#frm_data_get');
            var url = $frm.attr('action');
            Panel.resetForm( Panel.edit.form() );
            Panel.edit.status.loading();
            $.ajax({
                type: 'GET',
                url: url,
                dataType: 'json',
                data: $frm.serialize() + '&id=' + id
            }).done(function(data) {
                //console.log(data);
                if (data['ok']) {
                    if (typeof fill_inputs == "undefined" || fill_inputs) {
                        Panel.objectToInputs(Panel.edit.form(), data);
                    }
                    if (typeof fn_callback == "function") {
                        fn_callback(data);
                    }
                    else if (typeof afterPanelEditLoaded == 'function') {
                        afterPanelEditLoaded(data);
                    }
                    Panel.edit.status.restore( data['title'] );
                }
                else {
                    Panel.edit.status.error( data['err'] );
                }
            }).fail(function(data) {
                console.log(data); //failed
                Panel.edit.status.error(data['responseText'].substr(0, 200));
            });
        },

        //sends form data to server
        save: function() {
            Panel.edit.status.saving();
            var $frm = Panel.edit.form();
            var url = $frm.attr('action');
            var with_file = $frm.hasClass('with-file');
            var data_values;
            if (with_file) {
                data_values = new FormData($frm[0]);
            }
            else {
                data_values = $frm.serialize();
            }
            $.ajax({
                type: 'POST',
                url: url,
                dataType: 'json',
                data: data_values,
                processData: !with_file,
                contentType: !with_file ? 'application/x-www-form-urlencoded; charset=UTF-8' : false
            }).done(function(data) {
                //console.log(data);
                if (data['ok']) {
                    Panel.edit.status.saved();
                }
                else {
                    Panel.edit.status.error(data['err']);
                }
            }).fail(function(data) {
                console.log(data); //failed
                Panel.edit.status.error(data['responseText'].substr(0, 200));
            });
        },

        //sets a style for the edit-new panel
        status: {
            loading: function() {
                $('#edit_icon').removeClass(Panel.icons).addClass(Panel.loadingIcon);
                $('#edit_lbl').html('Cargando...');
                App.blockUI( $('#edit_panel') );
            },
            saving: function() {
                $('#edit_icon').removeClass(Panel.icons).addClass(Panel.loadingIcon);
                $('#edit_lbl').html('Guardando...');
                App.blockUI( $('#edit_panel') );
            },
            saved: function() {
                $('#edit_icon').removeClass(Panel.allIcons()).addClass('fa-check');
                $('#edit_lbl').html('Guardado.');
                Panel.edit.collapse();
                //binds an event to restore status if user enters new data
                $('#frm_data_edit').find('input').on('change.after_saved', function() {
                    Panel.edit.status.restore();
                    //unbinds the event; no longer needed
                    $('#frm_data_edit').find('input').off('change.after_saved');
                });
                App.unblockUI( $('#edit_panel') );
            },
            error: function(err) {
                $el = $('#edit_icon').removeClass(Panel.allIcons()).addClass('fa-exclamation-triangle');
                $('#edit_lbl').html(err);
                $el = $el.parent().addClass('animated flash');
                setTimeout(function() {
                    $el.removeClass('animated');
                },4000);
                Panel.edit.expand();
                App.unblockUI( $('#edit_panel') );
            },
            restore: function(title) {
                title_lbl = typeof title != 'string' ? 'Modificar' : title;
                $('#edit_icon').removeClass(Panel.allIcons()).addClass('fa-pencil').parent().removeClass('flash');;
                $('#edit_lbl').html( title_lbl );
                Panel.edit.expand();
                App.unblockUI( $('#edit_panel') );
            }
        }
    },

    status: {
        loading: function( $panel ) {
            $panel.find('.panel_icon').removeClass(Panel.icons).addClass(Panel.loadingIcon);
            $panel.find('.panel_lbl').html('Cargando...');
            App.blockUI( $panel );
        },
        saving: function( $panel ) {
            $panel.find('.panel_icon').removeClass(Panel.icons).addClass(Panel.loadingIcon);
            $panel.find('.panel_lbl').html('Guardando...');
            App.blockUI( $panel );
        },
        saved: function( $panel, msg ) {
            $panel.find('.panel_icon').removeClass(Panel.allIcons()).addClass('fa-check');
            $panel.find('.panel_lbl').html(typeof msg == 'string' ? msg : 'Guardado.');
            Panel.collapse( $panel );
            App.unblockUI( $panel );
        },
        error: function($panel, err) {
            $el = $panel.find('.panel_icon').removeClass(Panel.allIcons()).addClass('fa-exclamation-triangle');
            $panel.find('.panel_lbl').html(err);
            $el = $el.parent().addClass('animated flash');
            setTimeout(function() {
                $el.removeClass('animated');
            },4000);
            Panel.expand( $panel );
            App.unblockUI( $panel );
        },
        restore: function($panel, title, fa_icon) {
            $panel.find('.panel_icon').removeClass(Panel.allIcons()).addClass(fa_icon).parent().removeClass('flash');;
            $panel.find('.panel_lbl').html( title );
            Panel.expand( $panel );
            App.unblockUI( $panel );
        }
    }
};

//Binding
$('.btn-add-new').click(function() {
    Panel.create.resetForm();
    Panel.create.status.restore();
    Panel.create.show();
});

$('#frm_data_search').submit(function(e) {
    setTimeout(Panel.search.find(), 100);
    e.preventDefault();
    return false;
});

$('#frm_data_new').submit(function(e) {
    setTimeout(Panel.create.save(), 100);
    e.preventDefault();
    return false;
});

$('#frm_data_edit').submit(function(e) {
    setTimeout(Panel.edit.save(), 100);
    e.preventDefault();
    return false;
});

$('#frm_data_view').submit(function(e) {
    setTimeout(Panel.view.submit(), 100);
    e.preventDefault();
    return false;
});

//collapse/expand panel by title clicking
$('.box .box-title h4').click(function(e) {
    var $this = $(this).parent();

    var $t = $this.find('.collapse');
    if ($t.length) {
        $t.click();
    }
    else {
        $this.find('.expand').click();
    }
    e.stopPropagation();
    return false;
});

function bindNumberInputs() {
    $('.number-more').click(function() {
        var $number = $(this).closest('.input-group').find('.number-input');
        $number.val( (parseInt($number.val()) || 0) + 1 );
    });

    $('.number-less').click(function() {
        var $number = $(this).closest('.input-group').find('.number-input');
        var num = parseInt($number.val());
        $number.val( num > 0 ? (num - 1) : 0 );
    });
}
bindNumberInputs();

//setting time and date inputs
function setDatePicker(o, value) {
    if (typeof value == 'object' && value != null) {
        o.pickadate('picker').set('select', new Date(value.getUTCFullYear(), value.getUTCMonth(), value.getUTCDate()));
    }
    else if (value != '0000-00-00') {
        o.pickadate('picker').set('select', value);
    }
    else {
        o.pickadate('picker').set('clear');
    }
}

function setTimePicker(o, value) {
    if (value == null) {
        o.pickatime('picker').set('select', null);
        return true;
    }
    if (typeof value == 'object') {
        o.pickatime('picker').set('select', [value.getUTCHours(), value.getUTCMinutes()]);
        return true;
    }
    var t = value.split(' ');
    //"2015-01-26 13:48:00"
    if (t.length == 2) {
      t = t[1].split(':');
    }
    //"13:48:00"
    else if (t.length == 1) {
      t = t[0].split(':');
    }

    if (t.length >= 2) {
        if (t[0] == 0 && t[1] == 0) {
            o.pickatime('picker').clear();
            return false;
        }
        o.pickatime('picker').set('select', [t[0], t[1]]);
        var hour = parseInt(t[0]);
        var ampm;
        if (hour > 12) {
            hour -= 12;
            ampm = 'PM';
        }
        else {
            ampm = 'AM';
        }
        o.val((hour < 10 ? '0' : '') + hour + ':' + t[1] + ' ' + ampm);
    }
    else o.val( value );
    return true;
}

function isset($var) {
  return typeof $var != 'undefined';
}

function addClassIf($obj, class_name, add) {
    if (add) {
        $obj.addClass(class_name);
    }
    else {
        $obj.removeClass(class_name);
    }
}

//reloading ajax data for allowed panels
function reloadPanel(box) {
    switch (box.attr('id')) {
        case 'search_panel':
            Panel.search.update();
            break;
        case 'view_panel':
            Panel.view.reload();
    }
}

function submitFormDoneDefault($frm, data) {
    var $alert = $frm.closest('.modal-body').find('.alert');
    if (data['ok'] == 1) {
        $alert.hide().find('.msg').html( '' );
        $frm.closest('.modal').modal('hide');
    }
    else {
        $alert.find('.msg').html( data['err'] );
        $alert.removeClass('hidden').show().addClass('animated shake');
        setTimeout(function() {
            $alert.removeClass('animated');
        }, 500);
    }
}

function submitForm($frm, fn_done, fn_fail, method, extra_data, url_alt) {
    var frm_method = $frm.attr('method');
    method = typeof method != 'string' ? (typeof frm_method != 'undefined' ? frm_method : 'POST') : method;
    extra_data = typeof extra_data != 'string' ? '' : extra_data;
    var url = typeof url_alt != 'string' ? $frm.attr('action') : url_alt;
    $.ajax({
        type: method,
        url: url,
        dataType: 'json',
        data: $frm.serialize() + extra_data // serializes the form's elements.
    }).done(function(data) {
        console.log(data);
        if (typeof fn_done == 'function') {
            fn_done($frm, data);
        }
        else {
            submitFormDoneDefault($frm, data);
        }
    }).fail(function(data) {
        console.log(data); //failed
        if (typeof fn_fail == 'function') {
            fn_fail(data);
        }
        else {
            alert('Error de conexión.');
        }
    });
}