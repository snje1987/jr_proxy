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

})(jQuery);