# Catcher [![Build Status](https://secure.travis-ci.org/alexeyshockov/catcher.png)](http://travis-ci.org/alexeyshockov/catcher)

## Goal

Simple, usable and transparent cache layer

## Installation

...

## Usage

To use catcher you need some web server, PHP (5.3 or higher) and MongoDB on your server.

Example nginx configuration will present below.

``` nginx
# Balancer.
server {
    listen 80;

    error_page @app;

    # Catcher.
    location / {
        proxy_intercept_errors on;

        if ($request_method = GET) {
            proxy_pass http://127.0.0.1:8080/pages?url=$request_uri;

            break;
        }

        return 404;
    }

    location @app {
        proxy_pass http://127.0.0.1:8081;
    }
}

# Catcher.
server {
    listen 127.0.0.1:8080;

    root /var/www/catcher/web;

    index index.php;

    # Example for default PHP-FPM installation.
    location ~ \.php {
        fastcgi_pass 127.0.0.1:9000;

        include fastcgi_params;

        fastcgi_split_path_info       ^(.+\.php)(/.+)$;
        fastcgi_param PATH_INFO       $fastcgi_path_info;
        fastcgi_param PATH_TRANSLATED $document_root$fastcgi_path_info;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}

# Application.
server {
    listen 127.0.0.1:8081;

    # ...
}
```

## Performance

...

## Contributing

Test suite should be pass (PHPUnit required).
