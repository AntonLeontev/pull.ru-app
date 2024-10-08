<?php

namespace Deployer;

require 'recipe/laravel.php';

// Config

set('repository', 'https://github.com/AntonLeontev/pull.ru-app.git');
set('keep_releases', 4);

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

// Tasks
task('supervisor:restart', function () {
    run('sudo supervisorctl restart all');
});

task('build', function () {
    cd('{{release_path}}');
    run('npm install');
    run('npm run build');
});

// Hosts

host('5.35.83.237')
    ->set('remote_user', 'deployer')
    ->set('deploy_path', '~/pull.ru-app');

// Hooks

after('deploy:failed', 'deploy:unlock');
after('deploy:success', 'supervisor:restart');
after('deploy:vendors', 'build');
