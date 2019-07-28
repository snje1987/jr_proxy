<div id="pop" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        </div>
    </div>
</div>
<script type="text/javascript">
    (function ($) {
        $(document).ready(function () {
            $('.pop_btn').pop_btn({target: '#pop'});
        });
    })(jQuery);
</script>
<?php if (!empty($mbx)) { ?>
    <ol class="breadcrumb">
        <?php foreach ($mbx as $k => $v) { ?>
            <li><a href="<?= $v == '' ? 'javascript:void(0);' : $v ?>"><?= $k ?></a></li>
        <?php } ?>
    </ol>
<?php } ?>