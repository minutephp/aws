<?php

/** @var Router $router */
use Minute\Model\Permission;
use Minute\Routing\Router;

$router->get('/admin/aws/setup', null, 'admin', 'm_configs[type] as configs')
       ->setReadPermission('configs', 'admin')->setDefault('type', 'aws');
$router->post('/admin/aws/setup', null, 'admin', 'm_configs as configs')
       ->setAllPermissions('configs', 'admin');

$router->get('/admin/aws/deploy', null, 'admin', 'm_configs[type] as configs')
       ->setReadPermission('configs', 'admin')->setDefault('type', 'aws');
$router->post('/admin/aws/deploy', null, 'admin', 'm_configs as configs')
       ->setAllPermissions('configs', 'admin');

$router->get('/admin/aws/cron', null, 'admin', 'm_configs[type] as configs')
       ->setReadPermission('configs', 'admin')->setDefault('type', 'aws');
$router->post('/admin/aws/cron', null, 'admin', 'm_configs as configs')
       ->setAllPermissions('configs', 'admin');

$router->get('/admin/aws/cdn', null, 'admin', 'm_configs[type] as configs')
       ->setReadPermission('configs', 'admin')->setDefault('type', 'aws');
$router->post('/admin/aws/cdn', 'Admin/Aws/Cdn', 'admin', 'm_configs as configs')
       ->setAllPermissions('configs', 'admin');

$router->get('/admin/aws/db', null, 'admin', 'm_configs[type] as configs')
       ->setReadPermission('configs', 'admin')->setDefault('type', 'aws');
$router->post('/admin/aws/db', null, 'admin', 'm_configs as configs')
       ->setAllPermissions('configs', 'admin');
$router->post('/admin/aws/db/copy', 'Admin/Aws/DbCopy', 'admin');

$router->get('/admin/aws/migrations', null, 'admin');
$router->post('/admin/aws/migrations/run', 'Admin/Migrations/Run', 'admin');

$router->get('/_aws/health', 'Aws/Health.php', false)
       ->setDefault('_noView', true);

$router->post('/_aws/deploy', 'Aws/Deploy', false);
$router->post('/_aws/sns/bounce', 'Aws/SnsHandler.php@bounce', false);
$router->post('/_aws/sns/spam', 'Aws/SnsHandler.php@spam', false);
