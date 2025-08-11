<?php
use DeliciousBrains\WP_Offload_Media\Providers\Storage\AWS_Provider;

class WPOM_UpCloud_Provider extends AWS_Provider
{
    protected static $provider_service_name = 'Amazon S3 (UpCloud Object Storage)';
}
