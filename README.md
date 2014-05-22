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

