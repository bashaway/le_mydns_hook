MyDNSを利用したLet's Encryptのワイルドカードでマルチドメインな証明書発行用のフックです。
サブドメインも含めて設定できます。

参考にしたのは、MyDNSのDirectEditというスクリプトです。
これを複数のドメインに対応したものになります。

## 利用シーン
MyDNSで以下のドメインとサブドメインを運用している場合。

* ドメイン
	* ドメイン名：example.com
	* マスターID：mydns123456
	* パスワード：mydnspassword
* サブドメイン
	* サブドメイン名：sub.example.com
	* マスターID：mydns654321
	* パスワード：subdompassword


以下の証明書を発行することで、サブドメインを含めたドメイン以下の書くサーバで同じ証明書が利用できるようになります。

|項目|値|
|:---:|:---|
|CN |\*.example.com|
|SAN|\*.example.com|
|SAN|\*.sub.example.com|
|SAN|example.com|



## 対象機器および環境
* CentOS7(7.7.1908)
* certbot(1.0.0)
* php(5.4.16)


## 利用方法

### 1.準備
```
yum -y install epel-release
yum -y install php php-mbstring certbot
git clone https://github.com/bashaway/le_mydns_hook
cd ./le_mydns_hook
```


### 2.MyDNSアカウント情報の修正

./le_mydns_hook/accounts.conf にMyDNSのアカウント情報を記載します。

```
vi accounts.conf
----------8<-----(snip)-----8<----------
$MYDNS_ID['ドメイン名']  = 'マスターID';
$MYDNS_PWD['ドメイン名'] = 'パスワード';
----------8<-----(snip)-----8<----------
```

例えば、上記の例の場合 accounts.confは以下のように修正します。

```
----------8<-----(snip)-----8<----------
$MYDNS_ID['example.com']  = 'mydns123456';
$MYDNS_PWD['example.com'] = 'mydnspassword';
$MYDNS_ID['sub.example.com']  = 'mydns654321';
$MYDNS_PWD['sub.example.com'] = 'subdompassword';
----------8<-----(snip)-----8<----------
```


### 3.証明書発行

```
certbot certonly --manual \
 --server https://acme-staging-v02.api.letsencrypt.org/directory \
 --preferred-challenges dns-01 \
 --agree-tos --no-eff-email \
 --manual-public-ip-logging-ok \
 --manual-auth-hook ./le_mydns_hook/regist.php \
 --manual-cleanup-hook ./le_mydns_hook/delete.php \
 -m bashaway@gmail.com \
 -d *.prosper2.net \
 -d *.branch01.prosper2.net \
 -d prosper2.net \
```

たぶん、以下のように、CNがワイルドカードで、SAN付きマルチドメインになっているはずです。
```
openssl x509 -in /etc/letsencrypt/archive/example.com/cert1.pem -text | egrep "CN|DNS"
        Issuer: CN=Fake LE Intermediate X1
        Subject: CN=*.example.com
                DNS:*.sub.example.com, DNS:*.example.com, DNS:example.com
```



### 4.自動更新のチェック

チェックのために、--force-renewalをつけてみます。
```
certbot certonly --manual \
 --server https://acme-staging-v02.api.letsencrypt.org/directory \
 --preferred-challenges dns-01 \
 --agree-tos --no-eff-email \
 --manual-public-ip-logging-ok \
 --manual-auth-hook ./le_mydns_hook/regist.php \
 --manual-cleanup-hook ./le_mydns_hook/delete.php \
 -m bashaway@gmail.com \
 -d *.prosper2.net \
 -d *.branch01.prosper2.net \
 -d prosper2.net \
 --force-renewal
```

おそらく、以下のように更新後のものが発行されていると思います。
```
$ ls -1 /etc/letsencrypt/archive/example.com/cert*
/etc/letsencrypt/archive/example.com/cert1.pem
/etc/letsencrypt/archive/example.com/cert2.pem
```

## 参考
https://github.com/disco-v8/DirectEdit
