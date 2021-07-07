FROM ubuntu:latest
ENV DEBIAN_FRONTEND=noninteractive
ENV TZ="Europe/Paris"
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && \
    echo $TZ > /etc/timezone
RUN apt-get update && \
    apt-get upgrade -y && \
    apt-get install -y php-fpm nginx php-ldap && \
    mkdir -p /app && \
RUN echo "upstream php {" > /etc/nginx/sites-available/default && \
    echo "        server unix:/run/php/php7.4-fpm.sock;" >> /etc/nginx/sites-available/default && \
    echo "        server 127.0.0.1:9000;" >> /etc/nginx/sites-available/default && \
    echo "}" >> /etc/nginx/sites-available/default && \
    echo "server {" >> /etc/nginx/sites-available/default && \
    echo "        server_name signature.creps-idf.fr;" >> /etc/nginx/sites-available/default && \
    echo "        root /app;" >> /etc/nginx/sites-available/default && \
    echo "        index index.php;" >> /etc/nginx/sites-available/default && \
    echo "        location / {" >> /etc/nginx/sites-available/default && \
    echo "                try_files \$uri \$uri/ /index.php?\$args;" >> /etc/nginx/sites-available/default && \
    echo "        }" >> /etc/nginx/sites-available/default && \
    echo "        location ~ \.php\$ {" >> /etc/nginx/sites-available/default && \
    echo "                include fastcgi_params;" >> /etc/nginx/sites-available/default && \
    echo "                fastcgi_intercept_errors on;" >> /etc/nginx/sites-available/default && \
    echo "                fastcgi_pass php;" >> /etc/nginx/sites-available/default && \
    echo "                fastcgi_param  SCRIPT_FILENAME \$document_root\$fastcgi_script_name;" >> /etc/nginx/sites-available/default && \
    echo "        }" >> /etc/nginx/sites-available/default && \
    echo "        location ~* \\.(js|css|png|jpg|jpeg|gif|ico)\$ {" >> /etc/nginx/sites-available/default && \
    echo "                expires max;" >> /etc/nginx/sites-available/default && \
    echo "                log_not_found off;" >> /etc/nginx/sites-available/default && \
    echo "        }" >> /etc/nginx/sites-available/default && \
    echo "}" >> /etc/nginx/sites-available/default && \
    echo "#!/bin/sh" > /run.sh && \
    echo "nginx" >> /run.sh && \
    echo "rm /etc/php/7.4/fpm/conf.d/10-opcache.ini" >> /run.sh && \
    echo "service php7.4-fpm start" >> /run.sh && \
    echo "service mysql start" >> /run.sh && \
    echo "chown www-data:www-data -R /app" >> /run.sh && \
    echo "tail -f /var/log/nginx/*" >> /run.sh && \
    chmod +x /run.sh
CMD ["/run.sh"]
