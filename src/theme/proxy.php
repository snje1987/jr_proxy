function FindProxyForURL(url, host)
{
    proxy = "PROXY <?= $host ?>:14201";
    if (shExpMatch(host, "*.jr.moefantasy.com"))
        return proxy;
    if (shExpMatch(host, "version.channel.jr.moefantasy.com"))
        return proxy;
    return "DIRECT";
}