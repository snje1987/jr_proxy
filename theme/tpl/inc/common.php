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
            $('.ajax').ajax_link();
        });
    })(jQuery);
</script>
<ul class="nav nav-tabs" style="margin-bottom:20px;">
    <li<?= $cur_tab == '/' ? ' class="active"' : '' ?>><a href="/">首页</a></li>
    <li<?= $cur_tab == '/ship/index' ? ' class="active"' : '' ?>><a href="/ship/index?uid=<?= isset($_GET['uid']) ? $_GET['uid'] : '' ?>">强化计算器</a></li>
    <li<?= $cur_tab == '/warlog/index' ? ' class="active"' : '' ?>><a href="/warlog/index">战斗记录</a></li>
    <li<?= $cur_tab == '/ship/fleet' ? ' class="active"' : '' ?>><a href="/ship/fleet?uid=<?= isset($_GET['uid']) ? $_GET['uid'] : '' ?>">编队辅助</a></li>
</ul>