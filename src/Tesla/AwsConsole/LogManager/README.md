# Tesla AWS Log Manager

Console command to move log files to an S3 Storage

## Usage:

execute (with) cron as root:

    php /opt/console/prod/latest/console aws:log-move --config-dir=/etc/opt/console/aws-logmove.d

## Configuration

Create config files for each directory you want to move in /etc/opt/console/aws/logmove.d:

```
{
    "dir": "/var/log/mylogdir",
    "name": "*.gz",
    "deleteAfterTransfer": true,
    "s3Bucket": "mybucketname",
    "s3Dir": "/logs/myserver-name"
}
```

(make sure you have AWS set up and the factory available through app['aws'])
