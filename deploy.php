<?php
namespace Deployer;

require 'recipe/common.php';

// Configuration

set('repository', 'git@github.com:beer/speculator.git');
//set('git_tty', true); // [Optional] Allocate tty for git on first deployment
set('shared_files', ['config/database.json']);
set('shared_dirs', []);
set('writable_dirs', []);
set('default_stage', 'beta');


// Hosts

host('speculator.im')
    ->user('deployer')
    ->stage('prod')
    ->set('deploy_path', '/srv/www/speculator');
    
host('beta.speculator.im')
    ->user('deployer')
    ->stage('beta')
    ->set('deploy_path', '/srv/www/beta.speculator');    


// Tasks

/*
// 現在沒有用 php-fpm
desc('Restart PHP-FPM service');
task('php-fpm:restart', function () {
    // The user must have rights for restart service
    // /etc/sudoers: username ALL=NOPASSWD:/bin/systemctl restart php-fpm.service
    run('sudo systemctl restart php-fpm.service');
});
after('deploy:symlink', 'php-fpm:restart');
*/

desc('Deploy your project');
task('deploy', [
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:writable',
    //'deploy:vendors', // 還沒有用 composer
    'deploy:clear_paths',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
    'success'
]);

task('test', function () {
        writeln('Hello world');
});
task('pwd', function () {
    $result = run('pwd');
    writeln("Current dir: $result");
});

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
