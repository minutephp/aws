<?php

/** @var Binding $binding */
use Minute\Apache\ApacheFile;
use Minute\Beanstalk\BeanstalkFile;
use Minute\Docker\DockerFile;
use Minute\Event\AdminEvent;
use Minute\Event\Binding;
use Minute\Event\DockerEvent;
use Minute\Event\GitEvent;
use Minute\Event\MigrationEvent;
use Minute\Event\RawMailEvent;
use Minute\Event\TodoEvent;
use Minute\Event\UserUploadEvent;
use Minute\Git\GitStatus;
use Minute\Mail\SesMailer;
use Minute\Menu\AwsMenu;
use Minute\Migration\MigrationStatus;
use Minute\Todo\AwsTodo;
use Minute\Upload\S3Uploader;

$binding->addMultiple([
    //aws
    ['event' => RawMailEvent::MAIL_SEND_RAW, 'handler' => [SesMailer::class, 'sendMail']],
    ['event' => AdminEvent::IMPORT_ADMIN_MENU_LINKS, 'handler' => [AwsMenu::class, 'adminLinks']],

    ['event' => UserUploadEvent::USER_UPLOAD_FILE, 'handler' => [S3Uploader::class, 'upload']],
    ['event' => DockerEvent::DOCKER_INCLUDE_FILES, 'handler' => [DockerFile::class, 'create'], 'priority' => 100],
    ['event' => DockerEvent::DOCKER_INCLUDE_FILES, 'handler' => [ApacheFile::class, 'config'], 'priority' => 1],
    ['event' => DockerEvent::DOCKER_INCLUDE_FILES, 'handler' => [ApacheFile::class, 'finish'], 'priority' => -99],
    ['event' => DockerEvent::DOCKER_INCLUDE_FILES, 'handler' => [DockerFile::class, 'finish'], 'priority' => -100],
    //ebextensions
    ['event' => DockerEvent::DOCKER_INCLUDE_FILES, 'handler' => [BeanstalkFile::class, 'create']],

    ['event' => GitEvent::IMPORT_GIT_STATUS, 'handler' => [GitStatus::class, 'getStatus']],
    ['event' => MigrationEvent::IMPORT_MIGRATION_STATUS, 'handler' => [MigrationStatus::class, 'getStatus']],

    //tasks
    ['event' => TodoEvent::IMPORT_TODO_ADMIN, 'handler' => [AwsTodo::class, 'getTodoList']],
]);