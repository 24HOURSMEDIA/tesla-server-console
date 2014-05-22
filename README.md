tesla-server-console
=================

Monitoring application

# Installation

**download composer**

    curl -sS https://getcomposer.org/installer | php


**download and install application from packagist:**

     php composer.phar  create-project tesla/server-console --stability=dev

**configure nginx to listen on a port for the monitoring application:**

```
# listen on port 5000 to server monitoring programs
server {

	listen   5000; ## listen for ipv4
	#root   /...;

	location / {
		try_files $uri $uri/ =404;
		index  index.html index.htm index.php;
	}


	# pass the PHP scripts to FastCGI server listening unix socket
	location ~ ^/server-console/(.*)$ {
 		alias _____APPROOTDIR____/web;
 		set $file $1;
 		try_files $file @tesla-server-console;

 		# route to front controller
 		location ~ ^/server-console/(index|index_dev)\.php(/|$) {
 			set $script $1.php;
 			fastcgi_split_path_info ^(.+\.php)(/.*)$;
 			fastcgi_index index.php;
 			fastcgi_param  SCRIPT_FILENAME _____APPROOTDIR____/web/$1.php;
 			include fastcgi_buffer;
 			include fastcgi_params;
    		fastcgi_pass 127.0.0.1:9000;
 		}
	}
	location @tesla-server-console {
    		rewrite ^/server-console/(.*)$ /server-console/index.php/$1 last;
    }

	# deny access to .htaccess files, if Apache's document root  concurs with nginx's one
	location ~ /\.ht {
		deny  all;
	}
}

```

**copy default configuration to prod config:**

    cp config/parameters.json.dist config/parameters.json

and adjust settings (most notably data_dir)

**set permissions:**

    sudo chown -R www-data ./cache ./logs

**install crontab as root:**

    */1 * * * * APPDIR/console tesla:server-console:collect-stats 2>&1 >/dev/null

# Advanced installation

It is advised on AWS under nginx/php-fpm to run the console in a separate pool as the ec2-user, and give the user read access to log files etc.

```
; Start a new pool named tesla-server-console.
[tesla-server-console]
listen = 127.0.0.1:9500
listen.allowed_clients = 127.0.0.1
listen.owner = ec2-user
listen.group = ec2-user
listen.mode = 0666
user = ec2-user
group = ec2-user
pm = ondemand; 
pm.max_children = 8
pm.max_requests = 256; 
request_terminate_timeout = 120s			; The timeout for serving a single request after which the worker process will be killed. This option should be used when the 'max_execution_time' ini option (..)
security.limit_extensions = .php .php3 .php4 .php5 .phar	; Limits the extensions of the main script FPM will allow to parse.
; Pass environment variables like LD_LIBRARY_PATH. All $VARIABLEs are taken from the current environment. Default Value: clean env
;env[HOSTNAME] = $HOSTNAME
;env[PATH] = /usr/local/bin:/usr/bin:/bin
;env[TMP] = /tmp
;env[TMPDIR] = /tmp
;env[TEMP] = /tmp
; Additional php.ini defines, specific to this pool of workers. 
php_flag[display_errors] = on
php_admin_value[error_log] = /var/log/php-fpm/local-server-error.log
php_admin_flag[log_errors] = on
php_admin_value[memory_limit] = 32M
```

Give user access:

    setfacl -d -m group:ec2-user:rx /var/log/nginx
    setfacl -m group:ec2-user:rx /var/log/nginx
    setfacl -m group:ec2-user:rx /var/log/nginx/*

