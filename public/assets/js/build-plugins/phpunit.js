var phpunitPlugin = ActiveBuild.UiPlugin.extend({
    id: 'build-phpunit-errors',
    css: 'col-xs-12',
    title: Lang.get('phpunit'),
    lastData: null,
    displayOnUpdate: false,
    box: true,
    rendered: false,

    register: function() {
        var self = this;
        var query = ActiveBuild.registerQuery('phpunit-data', -1, {key: 'phpunit-data'})

        $(window).on('phpunit-data', function(data) {
            self.onUpdate(data);
        });

        $(window).on('build-updated', function() {
            if (!self.rendered) {
                self.displayOnUpdate = true;
                query();
            }
        });
    },

    render: function() {

        return $('<div class="table-responsive"><table class="table" id="phpunit-data">' +
            '<thead>' +
            '<tr>' +
                '<th>'+Lang.get('status')+'</th>' +
                '<th>'+Lang.get('test_message')+'</th>' +
                '<th>'+Lang.get('trace')+'</th>' +
                '<th>'+Lang.get('time')+'</th>' +
            '</tr>' +
            '</thead><tbody></tbody></table></div>');
    },

    onUpdate: function(e) {
        if (!e.queryData) {
            $('#build-phpunit-errors').hide();
            return;
        }

        this.rendered = true;
        this.lastData = e.queryData;

        var tests = this.lastData[0].meta_value;
        var thead = $('#phpunit-data thead tr');
        var tbody = $('#phpunit-data tbody');
        thead.empty().append('<th>'+Lang.get('status')+'</th><th>'+Lang.get('test_message')+'</th><th>'+Lang.get('trace')+'</th><th>'+Lang.get('time')+'</th>');
        tbody.empty();

        if (tests.length == 0) {
            $('#build-phpunit-errors').hide();
            return;
        }

        var counts = { success: 0, failed: 0, error: 0, skipped: 0, todo: 0 }, total = 0;

        for (var i in tests) {
            var severity = tests[i].severity || (tests[i].pass ? 'success' : 'failed'),
                label    = ('success' == severity) ? 'success' : (
                    ('error' == severity) ? 'danger' : 'warning'
                );
            
            if ('fail' === severity) {
                severity = 'failed';
            }

            var status        = $('<td><span class="label label-' + label + '">'+Lang.get(severity)+'</span></td>'),
                content       = $('<td></td>'),
                trace         = $('<td></td>'),
                message       = $('<div class="visible-line-breaks"></div>').appendTo(content),
                trace_message = $('<div class="visible-line-breaks"></div>').appendTo(trace),
                time          = $('<td></td>');

            if (tests[i].message) {
                message.text(tests[i].message);
            } else if (tests[i].test && tests[i].suite) {
                message.text(tests[i].suite + '::' + tests[i].test);
            } else {
                message.html('<i>' + Lang.get('test_no_message') + '</i>');
            }

            if (tests[i].data) {
                content.append('<div>' + this.repr(tests[i].data) + '</div>');
            }

            if (tests[i].trace && tests[i].trace.length) {
                trace_message.append(tests[i].trace);
            }
            
            if (tests[i].time) {
                time.append(tests[i].time);
            } else {
                time.append('0.000');
            }

            $('<tr></tr>').append(status).append(content).append(trace).append(time).appendTo(tbody);

            counts[severity]++;
            total++;
        }

        $('#build-phpunit-errors').show();
    },

    repr: function(data)
    {
        switch(typeof(data)) {
            case 'boolean':
                return '<span class="boolean">' + (data ? 'true' : 'false') + '</span>';
            case 'string':
                return '<span class="string">"' + data + '"</span>';
            case 'undefined': case null:
                return '<span class="null">null</span>';
            case 'object':
                var rows = [];
                if(data instanceof Array) {
                    for(var i in data) {
                        rows.push('<tr><td colspan="3">' + this.repr(data[i]) + ',</td></tr>');
                    }
                } else {
                    for(var key in data) {
                        rows.push(
                            '<tr>' +
                                '<td>' + this.repr(key) + '</td>' +
                                '<td>=&gt;</td>' +
                                '<td>' + this.repr(data[key]) + ',</td>' +
                            '</tr>');
                    }
                }
                return '<table>' +
                        '<tr><th colspan="3">array(</th></tr>' +
                        rows.join('') +
                        '<tr><th colspan="3">)</th></tr>' +
                    '</table>';
        }
        return '???';
    },

    buildTrace: function(trace){
        var list = '<ol reversed>';

        trace.forEach(function(line){
            list += '<li>' + line + '</li>';
        });
        list += '</ol>';

        return list;
    }
});

ActiveBuild.registerPlugin(new phpunitPlugin());
