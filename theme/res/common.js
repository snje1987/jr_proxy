(function ($) {
    $.fn.check_all = function (options) {
        var defaults = {
            'target': ''
        };
        $.extend(defaults, defaults, options);

        if (defaults.target == '') {
            return;
        }

        var $this = $(this);
        var target = $(defaults.target);

        $this.change(function () {
            var to_check = $this.is(':checked');
            for (var i = 0; i < target.length; i++) {
                if (to_check && !target.eq(i).is(':checked')) {
                    target.eq(i).prop('checked', 1);
                }
                else if (!to_check && target.eq(i).is(':checked')) {
                    target.eq(i).prop('checked', 0);
                }
            }
        });

        target.change(function () {
            var all_checked = true;
            for (var i = 0; i < target.length; i++) {
                if (!target.eq(i).is(':checked')) {
                    all_checked = false;
                }
            }
            if (all_checked) {
                $this.prop('checked', 1);
            }
            else {
                $this.prop('checked', 0);

            }
        });

    };
    $.fn.calc = function (options) {
        var defaults = {
            target: '',
            material: '',
            pop: '',
            table: ''
        };
        $.extend(defaults, defaults, options);

        $('#calc').click(function () {
            var target = $(defaults.target + ':checked').val();
            if (typeof target === 'undefined') {
                alert('必须选择强化目标');
                return;
            }
            var material = [];
            var checks = $(defaults.material);
            for (var i = 0; i < checks.length; i++) {
                if (checks.eq(i).is(':checked')) {
                    material[material.length] = checks.eq(i).val();
                }
            }

            if (material.length <= 0) {
                alert('必须选择强化素材');
                return;
            }

            var post = {};
            post.target = target;
            post.material = JSON.stringify(material);

            $.post('/boat/calc', post, null, 'json').done(function (data) {
                if (data.error !== 0) {
                    alert(data.msg);
                    return;
                }
                var result = data.result;
                var list = result.list;

                var table = $(defaults.table);
                table.html('');

                var point_attr = ['atk', 'torpedo', 'def', 'air_def'];
                var value_attr = ['2', '3', '4', '9'];

                for (var cid in list) {
                    var line = list[cid];
                    var tr = $('<tr></tr>');
                    tr.append('<td class="text-center">' + line.title + '</td>');
                    tr.append('<td class="text-center">' + line.count + '</td>');

                    var points = [];
                    var values = [];

                    for (var j = 0; j < 4; j++) {
                        points[j] = line.strengthenSupplyExp[point_attr[j]];
                        values[j] = line.dismantle[value_attr[j]];
                    }

                    tr.append('<td class="text-center">' + points.join(' / ') + '</td>');
                    tr.append('<td class="text-center">' + values.join(' / ') + '</td>');

                    table.append(tr);
                }

                var tr = $('<tr></tr>');
                tr.append('<td class="text-center">合计</td>');
                tr.append('<td class="text-center"></td>');

                var sum_point = [];
                var sum_dismantle = [];
                var exceed_point = [];
                for (var j = 0; j < 4; j++) {
                    sum_point[j] = result.sum_point[point_attr[j]];
                    sum_dismantle[j] = result.sum_dismantle[value_attr[j]];
                    exceed_point[j] = result.exceed_point[point_attr[j]];

                    if (exceed_point[j] > 0) {
                        sum_point[j] += '<sup style="color:red;">+' + exceed_point[j] + '</sup>';
                    }
                    else if (exceed_point[j] == 0) {
                        sum_point[j] += '<sup style="color:green;">' + exceed_point[j] + '</sup>';
                    }
                    else {
                        sum_point[j] += '<sup style="color:blue;">' + exceed_point[j] + '</sup>';
                    }

                }

                tr.append('<td class="text-center">' + sum_point.join(' / ') + '</td>');
                tr.append('<td class="text-center">' + sum_dismantle.join(' / ') + '</td>');

                table.append(tr);

                $(defaults.pop).modal();
            }).fail(function () {
                alert('发生错误');
            });
        });
    };
})(jQuery);