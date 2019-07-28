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
    $.fn.pop_btn = function (options) {
        var defaults = {
            target: null,
            data: null
        };
        $.extend(defaults, defaults, options);

        if (defaults.target === null || defaults.data === null) {
            return;
        }

        $(this).click(function () {
            var btn = $(this);

            var url = btn.attr('ohref');
            var data = null;

            if (typeof defaults.data === 'function') {
                data = defaults.data();
            }
            else {
                data = defaults.data;
            }

            if (data === null) {
                return;
            }

            $.post(url, data, null, 'html').done(function (html) {
                $(defaults.target + ' .modal-content').html(html);
                $(defaults.target).modal();
            }).fail(function () {
                alert('发生错误');
            });
        });
    };
    $.fn.ajax_link = function () {
        $(this).click(function () {
            var btn = $(this);
            var href = btn.attr('ohref');
            var question = btn.attr('question');

            if (typeof question !== 'undefined' && question != '') {
                if (!confirm(question)) {
                    return;
                }
            }

            $.get(href, null, null, 'json').done(function (data) {
                if (data.msg) {
                    alert(data.msg);
                }
                if (data.error == 0) {
                    location.reload();
                }
            }).fail(function () {
                alert('发生错误');
            });
        });
    };
})(jQuery);