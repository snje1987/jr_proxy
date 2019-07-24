function FindProxyForURL(url, host)
{
    proxy = "PROXY <?= $host ?>:14201";
    if (shExpMatch(host, "*.jr.moefantasy.com")){
        return proxy;
    }
<?php if($cache_res == 1){?>
    if (shExpMatch(host, "bshot.moefantasy.com")){
        return proxy;
    }
<?php }?>
    return "DIRECT";
}